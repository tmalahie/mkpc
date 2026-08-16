import { test, expect } from '@playwright/test';
import { sql } from './helpers/db';

// Covers the two lineup rules that are not visible from the MMR pass: the per-tier
// minimum (rule 3aa) and the POW Block opt-in (rule 3h).
test.describe.configure({ mode: 'serial' });

const TEST_ACCOUNTS = ['Wargor'];

async function resetLoungeQueues() {
	await sql(
		`UPDATE mklounge_queues q
		 JOIN mklounge_queue_members m ON m.queue = q.id
		 JOIN mkjoueurs j ON j.id = m.player
		 SET q.status = 'cancelled'
		 WHERE q.status NOT IN ('cancelled', 'finished') AND j.nom IN (?)`,
		[TEST_ACCOUNTS]
	);
	await sql(
		`UPDATE mklounge_queue_members m
		 JOIN mkjoueurs j ON j.id = m.player
		 SET m.dropped_at = NOW()
		 WHERE m.dropped_at IS NULL AND j.nom IN (?)`,
		[TEST_ACCOUNTS]
	);
}

test.beforeAll(resetLoungeQueues);
test.afterAll(resetLoungeQueues);

async function login(page, pseudo = 'wargor', code = 'aaaa') {
	const res = await page.request.post('http://127.0.0.1:8080/api/testcode.php', { form: { pseudo, code } });
	expect(Number(await res.text())).toBeGreaterThan(0);
	for (const header of res.headersArray().filter(h => h.name.toLowerCase() === 'set-cookie')) {
		const [name, value] = header.value.split(';')[0].split('=');
		await page.context().addCookies([{ name, value, domain: '127.0.0.1', path: '/' }]);
	}
}

// Puts the caller's queue into the voting phase without needing a full lineup, so the
// vote endpoint and the rules it produces can be exercised with a single account.
async function joinAndStartVoting(page, tierCode: string) {
	// a previous case may have launched a match, and leave.php cannot release that
	await resetLoungeQueues();
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = ?`, [tierCode]);
	const joined = await page.request.post('http://127.0.0.1:8080/api/lounge/join.php', {
		form: { tier: String(tier.id) },
	});
	const queueId = (await joined.json()).queue.id;
	await sql(`UPDATE mklounge_queues SET status = 'voting', ready_at = NOW() WHERE id = ?`, [queueId]);
	return queueId;
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
