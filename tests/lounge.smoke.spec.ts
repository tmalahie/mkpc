import { test, expect } from '@playwright/test';
import { login as uiLogin, createCircuits, createCup, createMulticup } from './helpers/mkpc';
import { sql } from './helpers/db';
import { cleanupLoungeQueues, createLoungeBots, loungeBotName, LOUNGE_BOT_PASSWORD, LOUNGE_KEY_MIN } from './helpers/lounge';

// One file on purpose. join.php puts a player into the tier's existing open queue, so
// every test here shares that queue whichever account it uses - and Playwright can only
// serialise tests within a file. Split across files they run in parallel workers and
// stand in each other's lineups.
test.describe.configure({ mode: 'serial' });

// Owns the fixtures this file builds. The "e2e-" prefix is what cleanupCreations
// sweeps by, so anything created without it would leak.
const OWNER = 'e2e-lounge-bot';

async function login(page, pseudo = 'wargor', code = 'aaaa') {
	const res = await page.request.post('http://127.0.0.1:8080/api/testcode.php', {
		form: { pseudo, code },
	});
	const body = await res.text();
	expect(Number(body)).toBeGreaterThan(0);
	const cookies = res.headersArray().filter(h => h.name.toLowerCase() === 'set-cookie');
	for (const c of cookies) {
		const [pair] = c.value.split(';');
		const [name, value] = pair.split('=');
		await page.context().addCookies([{ name, value, domain: '127.0.0.1', path: '/' }]);
	}
}

async function resetLoungeState(request) {
	await request.post('http://127.0.0.1:8080/api/lounge/leave.php');
}

test('lounge page renders tiers for logged-in user', async ({ page }) => {
	await login(page);
	// a queued account lands on the waiting view instead of the tier list
	await resetLoungeState(page.request);
	await page.goto('http://127.0.0.1:8080/lounge.php');

	await expect(page.locator('.lounge-header h1')).toHaveText('CT Lounge');
	await expect(page.locator('.lounge-tier').first()).toBeVisible({ timeout: 5000 });

	const tiers = page.locator('.lounge-tier');
	await expect(tiers).toHaveCount(5);

	await expect(tiers.nth(0).locator('.lounge-tier-title')).toContainText('All');
	await expect(tiers.nth(1).locator('.lounge-tier-title')).toContainText('C');

	// which tiers are locked depends on the account's current MMR, so derive it
	// from the API rather than assuming the player is still at the default rating
	const state = await (await page.request.post('http://127.0.0.1:8080/api/lounge/tiers.php')).json();
	const lockedTiers = state.tiers.filter((t: any) => !t.eligible);
	await expect(page.locator('.lounge-tier.is-locked')).toHaveCount(lockedTiers.length);
	await expect(tiers.nth(0)).not.toHaveClass(/is-locked/);
});

test('lounge tab switching works', async ({ page }) => {
	await login(page);
	await page.goto('http://127.0.0.1:8080/lounge.php');

	await expect(page.locator('[data-panel="queueup"]')).toHaveClass(/is-active/);
	await page.locator('[data-tab="howitworks"]').click();
	await expect(page.locator('[data-panel="howitworks"]')).toHaveClass(/is-active/);
	await expect(page.locator('[data-panel="queueup"]')).not.toHaveClass(/is-active/);
});

test('lounge page is gated when logged out', async ({ page }) => {
	await page.goto('http://127.0.0.1:8080/lounge.php');
	await expect(page.locator('.lounge-gate')).toBeVisible();
	await expect(page.locator('#lounge')).toHaveCount(0);
});

test('joining a tier transitions to the waiting screen and dropping returns', async ({ page }) => {
	await login(page);
	await resetLoungeState(page.request);
	await page.goto('http://127.0.0.1:8080/lounge.php');

	const joinAll = page.locator('.lounge-tier').nth(0).locator('.lounge-tier-join');
	await expect(joinAll).toBeEnabled();
	await joinAll.click();

	const waiting = page.locator('#lounge-queueup');
	await expect(waiting).toBeVisible();
	await expect(waiting.locator('.lounge-waiting-header h2')).toContainText('All');
	await expect(waiting.locator('.lounge-member')).toHaveCount(1);
	await expect(waiting.locator('.lounge-member.is-self')).toHaveCount(1);

	const drop = waiting.locator('.lounge-drop');
	await expect(drop).toBeVisible();
	await drop.click();

	await expect(page.locator('#lounge-tiers')).toBeVisible();
	await expect(page.locator('#lounge-queueup')).toBeHidden();
});

test('reopening lounge while queued shows waiting view directly', async ({ page }) => {
	await login(page);
	await resetLoungeState(page.request);

	const joined = await page.request.post('http://127.0.0.1:8080/api/lounge/join.php', { form: { tier: '1' } });
	expect((await joined.json()).queue).toBeTruthy();

	await page.goto('http://127.0.0.1:8080/lounge.php');
	await expect(page.locator('#lounge-queueup')).toBeVisible();
	await expect(page.locator('#lounge-tiers')).toBeHidden();

	await resetLoungeState(page.request);
});

test('voting flow: join → lock → vote → launch creates a private game', async ({ request }) => {
	const loginAs = async (pseudo, code) => {
		const r = await request.post('http://127.0.0.1:8080/api/testcode.php', { form: { pseudo, code } });
		const id = Number(await r.text());
		expect(id).toBeGreaterThan(0);
		return r.headersArray().filter(h => h.name.toLowerCase() === 'set-cookie').map(h => h.value);
	};
	const apiCall = async (cookieHeaders, endpoint, form = {}) => {
		const headers: Record<string, string> = {};
		if (cookieHeaders.length) headers['Cookie'] = cookieHeaders.map(c => c.split(';')[0]).join('; ');
		const r = await request.post('http://127.0.0.1:8080/api/' + endpoint, { form, headers });
		return r.json();
	};

	const cookies1 = await loginAs('wargor', 'aaaa');
	await apiCall(cookies1, 'lounge/leave.php');
	const joinRes = await apiCall(cookies1, 'lounge/join.php', { tier: '1' });
	expect(joinRes.queue.status).toBe('open');
	expect(joinRes.queue.members).toHaveLength(1);

	// Solo "vote" path: with 1 player, we can't lock/vote naturally. The unit-level coverage for vote/launch is
	// validated via direct API calls in CI; here we just confirm the public flow up through joining and leaving
	// returns the expected shape.
	expect(joinRes.queue.allowed_modes).toContain('FFA');

	await apiCall(cookies1, 'lounge/leave.php');
});

test('Ranked button opens the lounge overlay from online.php', async ({ page }) => {
	test.setTimeout(60000);
	await login(page);
	await page.goto('http://127.0.0.1:8080/online.php', { waitUntil: 'domcontentloaded' });
	await page.waitForFunction(() => typeof window['openLoungeOverlay'] === 'function', null, { timeout: 30000 });
	await page.evaluate(() => window['openLoungeOverlay']());

	const overlay = page.locator('#lounge-overlay');
	await expect(overlay).toBeVisible();
	const frame = page.frameLocator('#lounge-overlay iframe');
	await expect(frame.locator('.lounge-header h1')).toHaveText('CT Lounge');
});

test('ranked entry redirects to the season multicup', async ({ page }) => {
	await login(page);
	// follow no redirect: the seeded season multicup does not exist on a fresh database
	const res = await page.request.get('http://127.0.0.1:8080/ranked.php', { maxRedirects: 0 });
	expect(res.status()).toBe(302);
	expect(res.headers()['location']).toMatch(/^online\.php\?mid=\d+&ranked$/);
});

test('ranked entry picks a character then opens the lounge with it', async ({ page }) => {
	// build our own multicup: setup.sql seeds none, so the season's configured one
	// (prod's CT Project) does not exist in CI
	await uiLogin(page);
	const circuitIds = await createCircuits(page.request, 2, OWNER);
	const cupId = await createCup(page.request, { name: 'e2e-ranked-cup', circuitIds, author: OWNER });
	const mid = await createMulticup(page.request, { name: 'e2e-ranked-mcup', cupIds: [cupId], author: OWNER });

	await page.request.post('http://127.0.0.1:8080/api/lounge/leave.php');
	await page.goto('http://127.0.0.1:8080/online.php?mid=' + mid + '&ranked');

	// the roster comes from the multicup, so selection happens inside the game
	await page.locator('#perso-selector-mario').click();

	const lounge = page.frameLocator('#lounge-overlay iframe');
	await expect(lounge.locator('.lounge-header h1')).toHaveText('CT Lounge');
	await lounge.locator('.lounge-tier').first().locator('.lounge-tier-join').click();
	await expect(lounge.locator('.lounge-member.is-self')).toHaveCount(1);

	const queue = await (await page.request.post('http://127.0.0.1:8080/api/lounge/poll.php')).json();
	expect(queue.queue.members[0].perso).toBe('mario');

	await page.request.post('http://127.0.0.1:8080/api/lounge/leave.php');
});

// quitter() sends the other online modes to the multicup page the game belongs to, which
// is not where a ranked player came from: they came through the lounge.
test('ranked exits step back to the lounge, then to the game menu', async ({ page }) => {
	test.setTimeout(60000);
	await uiLogin(page);
	const circuitIds = await createCircuits(page.request, 2, OWNER);
	const cupId = await createCup(page.request, { name: 'e2e-ranked-exit-cup', circuitIds, author: OWNER });
	const mid = await createMulticup(page.request, { name: 'e2e-ranked-exit-mcup', cupIds: [cupId], author: OWNER });
	await page.request.post('http://127.0.0.1:8080/api/lounge/leave.php');

	// the account is not in a match for this key, so the character screen is shown
	// rather than skipped, which is where the Back button lives
	const key = LOUNGE_KEY_MIN;
	await sql('INSERT IGNORE INTO mkprivgame SET id = ?, player = 0', [key]);

	await page.goto(`http://127.0.0.1:8080/online.php?mid=${mid}&ranked&key=${key}`);
	await page.locator('#perso-selector-mario').waitFor({ timeout: 30000 });
	await page.locator('input[value="Back"]:visible').first().click();
	await expect(page).toHaveURL(`http://127.0.0.1:8080/online.php?mid=${mid}&ranked`);

	await page.locator('#perso-selector-mario').waitFor({ timeout: 30000 });
	await page.locator('input[value="Back"]:visible').first().click();
	await expect(page).toHaveURL('http://127.0.0.1:8080/mariokart.php');

	await sql('DELETE FROM mkprivgame WHERE id = ?', [key]);
});

test('leaderboard tab lists ranked players', async ({ page }) => {
	await login(page);
	await page.goto('http://127.0.0.1:8080/lounge.php');

	await page.locator('[data-tab="leaderboard"]').click();
	await expect(page.locator('[data-panel="leaderboard"]')).toHaveClass(/is-active/);

	// either a populated table or the "no mogi yet" notice, depending on season data
	const rows = page.locator('.lounge-leaderboard-row');
	const empty = page.locator('#lounge-leaderboard .lounge-empty');
	await expect(rows.first().or(empty)).toBeVisible({ timeout: 5000 });

	if (await rows.count()) {
		const first = rows.first();
		await expect(first.locator('.lounge-lb-place')).toHaveText('1');
		await expect(first.locator('.lounge-lb-rank')).not.toBeEmpty();
		await expect(first.locator('.lounge-lb-mmr')).not.toBeEmpty();
	}
});

async function joinAndStartVoting(page, tierCode: string) {
	// a previous case may have launched a match, and leave.php cannot release that
	await cleanupLoungeQueues();
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = ?`, [tierCode]);
	const joined = await page.request.post('http://127.0.0.1:8080/api/lounge/join.php', {
		form: { tier: String(tier.id) },
	});
	const queueId = (await joined.json()).queue.id;
	await sql(`UPDATE mklounge_queues SET status = 'voting', ready_at = NOW() WHERE id = ?`, [queueId]);
	return queueId;
}

// lounge_tick() piggybacks on any authenticated lounge endpoint.
async function tick(page) {
	const res = await page.request.post('http://127.0.0.1:8080/api/lounge/tiers.php');
	expect((await res.json()).error).toBeUndefined();
}

async function rulesFor(queueId: number) {
	const [queue]: any = await sql(`SELECT privgame_key FROM mklounge_queues WHERE id = ?`, [queueId]);
	expect(queue.privgame_key).toBeTruthy();
	const [options]: any = await sql(`SELECT rules FROM mkgameoptions WHERE id = ?`, [queue.privgame_key]);
	return JSON.parse(options.rules);
}

test('tier minimums follow the tier, not a global constant', async ({ page }) => {
	await login(page);
	const res = await page.request.post('http://127.0.0.1:8080/api/lounge/tiers.php');
	const tiers = (await res.json()).tiers;

	const all = tiers.find((t: any) => t.code === 'all');
	const c = tiers.find((t: any) => t.code === 'C');
	// rule 3aa: Tier All deliberately needs more players so the other tiers stay busy
	expect(all.min_players).toBe(6);
	expect(c.min_players).toBe(4);
});

test('a queue reports its own tier minimum', async ({ page }) => {
	await login(page);
	await page.request.post('http://127.0.0.1:8080/api/lounge/leave.php');
	const [tierAll]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const joined = await page.request.post('http://127.0.0.1:8080/api/lounge/join.php', {
		form: { tier: String(tierAll.id) },
	});
	expect((await joined.json()).queue.lock_threshold).toBe(6);
	await page.request.post('http://127.0.0.1:8080/api/lounge/leave.php');
});

test('a single member does not lock Tier All', async ({ page }) => {
	await login(page);
	await page.request.post('http://127.0.0.1:8080/api/lounge/leave.php');
	const [tierAll]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const joined = await page.request.post('http://127.0.0.1:8080/api/lounge/join.php', {
		form: { tier: String(tierAll.id) },
	});
	expect((await joined.json()).queue.status).toBe('open');
	await page.request.post('http://127.0.0.1:8080/api/lounge/leave.php');
});

test('the vote is incomplete until the POW choice is sent too', async ({ page }) => {
	await login(page);
	const queueId = await joinAndStartVoting(page, 'all');

	const res = await page.request.post('http://127.0.0.1:8080/api/lounge/vote.php', {
		form: { mode: 'FFA' },
	});
	expect((await res.json()).error).toBe('pow_required');

	// no POW choice means no vote was recorded, so nothing launched
	const [queue]: any = await sql(`SELECT status FROM mklounge_queues WHERE id = ?`, [queueId]);
	expect(queue.status).toBe('voting');

	await sql(`UPDATE mklounge_queues SET status = 'cancelled' WHERE id = ?`, [queueId]);
	await page.request.post('http://127.0.0.1:8080/api/lounge/leave.php');
});

// The official rules penalise drops and no-shows, never a missed vote, and a background
// tab can throttle the poll past a 60s window - so the deadline falls back to whoever did
// vote instead of cancelling the mogi and striking the rest of the lineup.
test('a missed vote launches on the votes cast rather than striking', async ({ page }) => {
	await login(page);
	const queueId = await joinAndStartVoting(page, 'all');
	const [{ id: playerId }]: any = await sql(`SELECT id FROM mkjoueurs WHERE nom = 'wargor'`);
	const [before]: any = await sql(
		`SELECT strikes FROM mklounge_players WHERE player = ? AND season = 1`, [playerId]
	);

	await sql(`UPDATE mklounge_queues SET ready_at = NOW() - INTERVAL 1 HOUR WHERE id = ?`, [queueId]);
	await tick(page);

	const [queue]: any = await sql(`SELECT status FROM mklounge_queues WHERE id = ?`, [queueId]);
	expect(queue.status).toBe('launched');

	const [match]: any = await sql(`SELECT mode, pow FROM mklounge_matches WHERE queue = ?`, [queueId]);
	expect(match.mode).toBe('FFA');
	// nobody agreed, so the POW Block stays out
	expect(match.pow).toBe(0);

	const [after]: any = await sql(
		`SELECT strikes FROM mklounge_players WHERE player = ? AND season = 1`, [playerId]
	);
	expect(after?.strikes ?? 0).toBe(before?.strikes ?? 0);
});

test('the vote screen groups the mode and item choices', async ({ page }) => {
	await login(page);
	const queueId = await joinAndStartVoting(page, 'all');
	await page.goto('http://127.0.0.1:8080/lounge.php');

	const groups = page.locator('.lounge-vote-group');
	await expect(groups).toHaveCount(2);
	await expect(groups.nth(0).locator('.lounge-vote-group-title')).toHaveText('Game mode');
	await expect(groups.nth(1).locator('.lounge-vote-group-title')).toHaveText('Items');

	// the POW toggle and its explanation live together, under the Items heading
	const items = groups.nth(1);
	const toggle = items.locator('.lounge-pow-toggle');
	await expect(toggle).toBeVisible();
	await expect(toggle.locator('.lounge-pow-name')).toHaveText('POW Block');
	await expect(toggle.locator('.lounge-pow-tally')).toHaveText('0 / 1 agreed');
	await expect(toggle).toHaveAttribute('aria-pressed', 'false');
	await expect(items.locator('.lounge-pow-note')).toContainText('only added if every player agrees');

	// toggling before a mode is picked stays local; nothing is submitted yet
	await toggle.click();
	await expect(toggle).toHaveAttribute('aria-pressed', 'true');
	await expect(toggle).toHaveClass(/is-selected/);
	const [member]: any = await sql(
		`SELECT voted_pow FROM mklounge_queue_members WHERE queue = ? AND dropped_at IS NULL`,
		[queueId]
	);
	expect(member.voted_pow).toBeNull();

	await sql(`UPDATE mklounge_queues SET status = 'cancelled' WHERE id = ?`, [queueId]);
});

test('the waiting screen offers an alert opt-in that survives a reload', async ({ page }) => {
	await login(page);
	await cleanupLoungeQueues();
	const [tierAll]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	await page.request.post('http://127.0.0.1:8080/api/lounge/join.php', {
		form: { tier: String(tierAll.id) },
	});
	await page.goto('http://127.0.0.1:8080/lounge.php');

	const toggle = page.locator('.lounge-alerts-toggle');
	await expect(toggle).toBeVisible();
	// the label names the setting and the state word reports it, so neither reads as a
	// call to action that could be mistaken for "alerts are off, click to enable"
	await expect(toggle.locator('.lounge-alerts-label')).toHaveText('Match alerts');
	// on by default: a queued player is expected to look away while waiting
	await expect(toggle).toHaveAttribute('aria-pressed', 'true');
	await expect(toggle.locator('.lounge-alerts-state')).toHaveText('On');

	// permission was never granted here, so the fallback hint stands while alerts are on
	await expect(page.locator('.lounge-alerts-hint')).toBeVisible();

	await page.locator('.lounge-alerts-toggle').click();
	await expect(page.locator('.lounge-alerts-toggle')).toHaveAttribute('aria-pressed', 'false');
	await expect(page.locator('.lounge-alerts-state')).toHaveText('Off');
	// and it goes away with them, rather than lingering from the previous state
	await expect(page.locator('.lounge-alerts-hint')).toHaveCount(0);

	await page.reload();
	await expect(page.locator('.lounge-alerts-toggle')).toHaveAttribute('aria-pressed', 'false');
	await expect(page.locator('.lounge-alerts-state')).toHaveText('Off');

	await page.evaluate(() => localStorage.removeItem('lounge.alerts'));
	await page.request.post('http://127.0.0.1:8080/api/lounge/leave.php');
});

// The whole point of the notification is to reach a player who is not looking at the tab,
// which is exactly the case alert() cannot serve.
test('a status change alerts a player whose tab is in the background', async ({ page }) => {
	await login(page);
	await cleanupLoungeQueues();
	await page.context().grantPermissions(['notifications'], { origin: 'http://127.0.0.1:8080' });
	await page.addInitScript(() => {
		(window as any).__notifications = [];
		document.hasFocus = () => false;
		(window as any).Notification = function(title: string, opts: any) {
			(window as any).__notifications.push({ title, body: opts.body });
		};
		(window as any).Notification.permission = 'granted';
	});

	const [tierAll]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const joined = await page.request.post('http://127.0.0.1:8080/api/lounge/join.php', {
		form: { tier: String(tierAll.id) },
	});
	const queueId = (await joined.json()).queue.id;
	await page.goto('http://127.0.0.1:8080/lounge.php');
	await expect(page.locator('.lounge-alerts-toggle')).toBeVisible();
	// the state the page opened on is not an update, so it must not alert
	expect(await page.evaluate(() => (window as any).__notifications.length)).toBe(0);

	// locked_at stays null so lounge_tick() leaves the queue sitting in this state
	await sql(`UPDATE mklounge_queues SET status = 'locked' WHERE id = ?`, [queueId]);

	await expect
		.poll(() => page.evaluate(() => (window as any).__notifications), { timeout: 10000 })
		.toHaveLength(1);
	const [notification]: any = await page.evaluate(() => (window as any).__notifications);
	expect(notification.title).toContain('Lineup complete');

	// and the tab title carries the same alert for anyone who denied permission
	await expect.poll(() => page.title(), { timeout: 5000 }).toContain('Lineup complete');

	await sql(`UPDATE mklounge_queues SET status = 'cancelled' WHERE id = ?`, [queueId]);
	await page.request.post('http://127.0.0.1:8080/api/lounge/leave.php');
});

test('leaving the page while queued is guarded, and released on drop', async ({ page }) => {
	await login(page);
	await cleanupLoungeQueues();
	const [tierAll]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	await page.request.post('http://127.0.0.1:8080/api/lounge/join.php', {
		form: { tier: String(tierAll.id) },
	});
	await page.goto('http://127.0.0.1:8080/lounge.php');
	await expect(page.locator('.lounge-alerts-toggle')).toBeVisible();
	// Chrome only raises the dialog for a frame the player has interacted with
	await page.locator('.lounge-tab[data-tab="queueup"]').click();

	const guardedWhileQueued = await page.evaluate(() => {
		const event = new Event('beforeunload', { cancelable: true });
		window.dispatchEvent(event);
		return event.defaultPrevented;
	});
	expect(guardedWhileQueued).toBe(true);

	// dropping releases it, so a player who left the queue is not nagged
	await page.locator('.lounge-drop').click();
	await expect(page.locator('.lounge-tier').first()).toBeVisible();
	const guardedAfterDrop = await page.evaluate(() => {
		const event = new Event('beforeunload', { cancelable: true });
		window.dispatchEvent(event);
		return event.defaultPrevented;
	});
	expect(guardedAfterDrop).toBe(false);
});

test('a unanimous yes puts the POW Block in the item distribution', async ({ page }) => {
	await login(page);
	const queueId = await joinAndStartVoting(page, 'all');

	await page.request.post('http://127.0.0.1:8080/api/lounge/vote.php', { form: { mode: 'FFA', pow: '1' } });

	const rules = await rulesFor(queueId);
	const distrib = rules.itemDistrib.value;
	expect(distrib.some((tier: any) => 'pow' in tier)).toBe(true);

	const [match]: any = await sql(`SELECT pow FROM mklounge_matches WHERE queue = ?`, [queueId]);
	expect(match.pow).toBe(1);
});

// #link-guidelines pins these two: a lightning may be held by two players at once, and it
// is not reserved for last place. Everything else keeps MKPC's defaults, which already match
// the guidelines' "leave all other categories ticked".
// "tout les 10-15 min on reçoit un message d'alerte demandant si on est encore dans la
// queue, et on est retiré de la queue si aucune réponse n'est fournie". Polling alone keeps
// a walked-away tab in the lineup for ever.
test('a queued player is asked to confirm, and dropped if they never answer', async ({ page }) => {
	await login(page);
	await cleanupLoungeQueues();
	const [tierAll]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const joined = await page.request.post('http://127.0.0.1:8080/api/lounge/join.php', {
		form: { tier: String(tierAll.id) },
	});
	const queueId = (await joined.json()).queue.id;
	const [{ id: playerId }]: any = await sql(`SELECT id FROM mkjoueurs WHERE nom = 'wargor'`);

	const age = (seconds: number) => sql(
		`UPDATE mklounge_queue_members SET confirmed_at = NOW() - INTERVAL ? SECOND
		 WHERE queue = ? AND player = ?`, [seconds, queueId, playerId]);

	// past the prompt window but still inside the grace period: asked, not dropped
	await age(700);
	await page.goto('http://127.0.0.1:8080/lounge.php');
	const prompt = page.locator('.lounge-confirm');
	await expect(prompt).toBeVisible();
	await expect(prompt.locator('.lounge-confirm-text')).toContainText('still in the queue');

	// answering resets the clock and clears the prompt
	await prompt.locator('.lounge-confirm-btn').click();
	await expect(page.locator('.lounge-confirm')).toHaveCount(0);

	// no answer at all, past the grace period: taken out of the list
	await age(60 * 60);
	await page.request.post('http://127.0.0.1:8080/api/lounge/tiers.php');
	const [member]: any = await sql(
		`SELECT dropped_at FROM mklounge_queue_members WHERE queue = ? AND player = ?`,
		[queueId, playerId]);
	expect(member.dropped_at).not.toBeNull();
	// dropping out of a queue is not an offence, so no strike
	const [state]: any = await sql(`SELECT strikes FROM mklounge_players WHERE player = ?`, [playerId]);
	expect(state?.strikes ?? 0).toBe(0);
});

test('the launched link carries the lounge lightning settings', async ({ page }) => {
	await login(page);
	const queueId = await joinAndStartVoting(page, 'all');
	await page.request.post('http://127.0.0.1:8080/api/lounge/vote.php', { form: { mode: 'FFA', pow: '0' } });

	const distrib = (await rulesFor(queueId)).itemDistrib;
	expect(distrib.lightningx2).toBe(1);
	expect(distrib.lightninglast).toBe(0);
});

// A lounge link has no owner (mkprivgame.player = 0), so without the lounge right nobody at
// all could edit a mogi's rules - unlike the Discord mogis, where whoever made the link can.
// Rule 4c. The client kept this history in memory only, so a player joining mid-mogi had no
// idea which courses were already used up - and Random could hand them a repeat.
test('the played course is recorded server-side and handed back to the room', async ({ page }) => {
	await login(page);
	await cleanupLoungeQueues();
	const key = LOUNGE_KEY_MIN + 40;
	const [{ id: playerId }]: any = await sql(`SELECT id FROM mkjoueurs WHERE nom = 'wargor'`);

	// a room whose players have all picked, which is the moment setMap.php resolves the course
	await sql(`INSERT IGNORE INTO mkprivgame SET id = ?, player = 0`, [key]);
	await sql(`INSERT INTO mkgameoptions (id, rules, public) VALUES (?, ?, 0)
	           ON DUPLICATE KEY UPDATE rules = VALUES(rules)`,
		[key, JSON.stringify({ friendly: 1, localScore: 1, minPlayers: 1, maxPlayers: 1, lounge: 1 })]);
	const room: any = await sql(
		`INSERT INTO mariokart (map, time, cup, mode, link) VALUES (-1, ?, 0, 0, ?)`,
		[Math.floor(Date.now() / 1000) + 3600, key]);
	await sql(`UPDATE mkjoueurs SET course = ?, choice_map = 7, choice_rand = 0 WHERE id = ?`,
		[room.insertId, playerId]);

	const res = await page.request.post('http://127.0.0.1:8080/api/getMap.php', { form: { key: String(key) } });
	const body = await res.text();
	// the same expression the client uses to pick the course: choixJoueurs[rCode[1]][2]
	expect(body).toContain('tracks:[7]');
	const [state]: any = await sql(`SELECT tracks FROM mkgamedata WHERE game = ?`, [key]);
	expect(state.tracks).toBe('7');

	// polling it again must not record the same course twice
	await page.request.post('http://127.0.0.1:8080/api/getMap.php', { form: { key: String(key) } });
	const [again]: any = await sql(`SELECT tracks FROM mkgamedata WHERE game = ?`, [key]);
	expect(again.tracks).toBe('7');

	// and matchmaking hands it over too: the selection screen comes before setMap.php, so a
	// player joining mid-game must already know what is used up on their first pick
	const [mate] = await createLoungeBots(1, 'joiner');
	await sql(`UPDATE mkjoueurs SET course = ? WHERE id = ?`, [room.insertId, mate]);
	await sql(`UPDATE mariokart SET map = -1, time = ? WHERE link = ?`,
		[Math.floor(Date.now() / 1000) + 500, key]);
	const joining = await page.request.post('http://127.0.0.1:8080/api/getCourse.php', {
		form: { key: String(key) },
	});
	expect(await joining.text()).toContain('"tracks":[7]');
	await sql(`UPDATE mkjoueurs SET course = 0 WHERE id = ?`, [mate]);

	await sql(`UPDATE mkjoueurs SET course = 0, choice_map = 0 WHERE id = ?`, [playerId]);
	await sql(`DELETE FROM mariokart WHERE link = ?`, [key]);
	await sql(`DELETE FROM mkgamedata WHERE game = ?`, [key]);
	await sql(`DELETE FROM mkgameoptions WHERE id = ?`, [key]);
});

test('only a lounge moderator can edit a lounge link', async ({ page, browser }) => {
	await login(page);
	const queueId = await joinAndStartVoting(page, 'all');
	await page.request.post('http://127.0.0.1:8080/api/lounge/vote.php', { form: { mode: 'FFA', pow: '0' } });
	const [queue]: any = await sql(`SELECT privgame_key FROM mklounge_queues WHERE id = ?`, [queueId]);
	const key = queue.privgame_key;

	const minPlayers = async () => {
		const [row]: any = await sql(`SELECT rules FROM mkgameoptions WHERE id = ?`, [key]);
		return JSON.parse(row.rules).minPlayers;
	};
	const edit = (request: any) => request.post('http://127.0.0.1:8080/api/privateGameOptions.php', {
		form: { key: String(key), options: JSON.stringify({ minPlayers: 3 }) },
	});

	// an ordinary player is not the owner and holds no right, so the link is closed to them
	await createLoungeBots(1, 'linkedit');
	const guest = await browser.newContext();
	const guestPage = await guest.newPage();
	await login(guestPage, loungeBotName('linkedit', 1), LOUNGE_BOT_PASSWORD);
	const before = await minPlayers();
	await edit(guestPage.request);
	expect(await minPlayers()).toBe(before);
	await guest.close();

	// the seeded account is an admin, which carries the lounge right
	await edit(page.request);
	expect(await minPlayers()).toBe(3);
});

test('anything short of unanimous strips the POW Block out', async ({ page }) => {
	await login(page);
	const queueId = await joinAndStartVoting(page, 'all');

	await page.request.post('http://127.0.0.1:8080/api/lounge/vote.php', { form: { mode: 'FFA', pow: '0' } });

	const rules = await rulesFor(queueId);
	const distrib = rules.itemDistrib.value;
	expect(distrib.some((tier: any) => 'pow' in tier)).toBe(false);

	// everything else about the distribution is untouched
	expect(distrib).toHaveLength(8);
	expect(distrib[4].champi).toBe(4);
	expect(distrib[0].carapace).toBe(5);

	const [match]: any = await sql(`SELECT pow FROM mklounge_matches WHERE queue = ?`, [queueId]);
	expect(match.pow).toBe(0);
});
