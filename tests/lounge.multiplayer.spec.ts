import { test, expect } from '@playwright/test';
import { sql } from './helpers/db';
import { cleanupLoungeQueues, createLoungeBots, loungeBotName, LOUNGE_BOT_PASSWORD } from './helpers/lounge';

// Real mogis, raced by one browser per member. They need the season's multicup to exist with
// tracks a kart finishes on its own - boosts, and holes that drop it onto the next lap - which
// the local database has and a fresh one (CI) does not, so they skip there. A run takes about
// a minute and a half per case.
test.describe.configure({ mode: 'serial' });

test.beforeAll(async () => {
	const [season]: any = await sql(`SELECT multicup_id FROM mklounge_seasons WHERE id = 1`);
	const [multicup]: any = await sql(`SELECT id FROM mkmcups WHERE id = ?`, [season.multicup_id]);
	test.skip(!multicup, 'needs the season multicup and its self-finishing tracks');
});

test.afterEach(async () => {
	await sql(`DELETE FROM mklounge_settings`);
});

async function login(page, pseudo: string, code: string) {
	const res = await page.request.post('http://127.0.0.1:8080/api/testcode.php', { form: { pseudo, code } });
	expect(Number(await res.text())).toBeGreaterThan(0);
	for (const header of res.headersArray().filter(h => h.name.toLowerCase() === 'set-cookie')) {
		const [name, value] = header.value.split(';')[0].split('=');
		await page.context().addCookies([{ name, value, domain: '127.0.0.1', path: '/' }]);
	}
}

async function tick(page) {
	await page.request.post('http://127.0.0.1:8080/api/lounge/tiers.php');
}

async function newPlayerPage(browser, tag: string, index: number) {
	const page = await (await browser.newContext({ viewport: { width: 1000, height: 700 } })).newPage();
	await login(page, loungeBotName(tag, index), LOUNGE_BOT_PASSWORD);
	return page;
}

const PERSOS = ['mario', 'luigi', 'peach', 'toad', 'yoshi', 'bowser', 'dk', 'wario'];

// A real FFA lineup, launched the way the lounge launches one, with every member looking at the
// room in a browser of their own. The characters are the ones they queued with, which the room
// preselects.
async function launchLineup(browser, tag: string, size: number) {
	await cleanupLoungeQueues();
	await sql(`DELETE FROM mklounge_settings`);
	await sql(`INSERT INTO mklounge_settings (name, value) VALUES ('races_per_match', 3)`);
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const players = await createLoungeBots(size, tag);
	const q: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, ready_at) VALUES (1, ?, 'voting', NOW() - INTERVAL 1 HOUR)`,
		[tier.id]);
	for (let i = 0; i < size; i++)
		await sql(`INSERT INTO mklounge_queue_members (queue, player, voted_mode, perso) VALUES (?, ?, 'FFA', ?)`,
			[q.insertId, players[i], PERSOS[i]]);

	const pages = [];
	for (let i = 1; i <= size; i++)
		pages.push(await newPlayerPage(browser, tag, i));
	await tick(pages[0]);
	await sql(`UPDATE mklounge_queues SET draft_turn_at = draft_turn_at - INTERVAL 1 MINUTE WHERE id = ?`, [q.insertId]);
	await tick(pages[0]);
	const [queue]: any = await sql(`SELECT status, privgame_key FROM mklounge_queues WHERE id = ?`, [q.insertId]);
	expect(queue.status).toBe('launched');
	const [season]: any = await sql(`SELECT multicup_id FROM mklounge_seasons WHERE id = 1`);
	const url = `http://127.0.0.1:8080/online.php?mid=${season.multicup_id}&ranked&key=${queue.privgame_key}`;
	await Promise.all(pages.map(p => p.goto(url, { waitUntil: 'domcontentloaded' })));
	return { key: queue.privgame_key, pages };
}

// The tracks finish on their own, but a player who touches nothing for a whole race is moved
// to the spectators at the next track choice, as an AFK player would be.
function keepActive(pages) {
	let stopped = false;
	(async () => {
		while (!stopped) {
			for (const page of pages) {
				if (!page.isClosed())
					await page.keyboard.press('ArrowUp').catch(() => {});
			}
			await new Promise(r => setTimeout(r, 2000));
		}
	})();
	return () => { stopped = true; };
}

// Moves when a race ends, not when it starts.
async function racesRun(key: number) {
	const [data]: any = await sql(`SELECT raceCount FROM mkgamedata WHERE game = ?`, [key]);
	return data ? Number(data.raceCount) : 0;
}

async function waitFor(what: string, check: () => Promise<boolean>, seconds: number) {
	for (let s = 0; s < seconds; s++) {
		if (await check())
			return;
		await new Promise(r => setTimeout(r, 1000));
	}
	throw new Error('timed out waiting for ' + what);
}

// The karts' name tags are plain elements, which is the one view of the race a test gets: the
// game keeps its state inside MarioKart().
function kartNames(page, tag: string): Promise<string[]> {
	return page.evaluate((prefix: string) => {
		const names = new Set<string>();
		for (const el of Array.from(document.querySelectorAll('div')))
			if (!el.children.length && el.textContent && el.textContent.startsWith(prefix))
				names.add(el.textContent);
		return Array.from(names).sort();
	}, loungeBotName(tag, 0).slice(0, -1)).catch(() => []);
}

test('a mogi voided at a race start sends the players still in the room to its results', async ({ browser }) => {
	test.setTimeout(300000);
	const { key, pages } = await launchLineup(browser, 'mpvoid', 5);
	const stop = keepActive(pages);
	try {
		await waitFor('race 1 to end', async () => (await racesRun(key)) >= 1, 150);

		// three of the five walk out, so the second race would need three bots
		for (const page of pages.slice(2))
			await page.context().close();
		const stayed = pages.slice(0, 2);

		await waitFor('the voided redirect', async () =>
			stayed.every(p => p.url().includes('lounge.php?key=' + key)), 150);
	}
	finally {
		stop();
	}
	const [match]: any = await sql(`SELECT cancelled_reason FROM mklounge_matches WHERE privgame_key = ?`, [key]);
	expect(match.cancelled_reason).toBe('too_many_bots');
	await expect(pages[0].locator('body')).toContainText('too many players left');
});

// A member who comes back mid-mogi can watch the race their substitute is driving under their
// own id. That bot must stay on the track for them, under its name - not be taken for the
// spectator's own kart and hidden, shifting every name after it by one.
test('a member watching their own substitute sees every kart', async ({ browser }) => {
	test.setTimeout(300000);
	const { key, pages } = await launchLineup(browser, 'mpspec', 4);
	const stop = keepActive(pages);
	try {
		await waitFor('race 1 to end', async () => (await racesRun(key)) >= 1, 150);
		await pages[3].context().close();
		// ready beforehand: a race is over in seconds, and the member has to walk in on it
		const watcher = await newPlayerPage(browser, 'mpspec', 4);
		const racing = async () => (await kartNames(pages[0], 'mpspec')).length > 0;
		await waitFor('the menu after race 1', async () => !(await racing()), 60);
		await waitFor('race 2 to start', racing, 90);

		await watcher.goto(pages[0].url(), { waitUntil: 'domcontentloaded' });
		// back mid-race, a member waits for the next one - unless they ask to watch this one
		await watcher.getByLabel('Spectator mode').check();
		await waitFor('the watcher to see the race', async () => (await kartNames(watcher, 'mpspec')).length > 0, 30);
		// every tag is drawn once the karts are placed, a moment after the first
		await watcher.waitForTimeout(2000);
		expect(await kartNames(watcher, 'mpspec')).toEqual([1, 2, 3, 4].map(i => loungeBotName('mpspec', i)));
	}
	finally {
		stop();
	}
});
