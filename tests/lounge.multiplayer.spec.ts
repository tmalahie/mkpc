import { test, expect } from '@playwright/test';
import { sql } from './helpers/db';
import { cleanupLoungeQueues, createLoungeBots, loungeBotName, LOUNGE_BOT_PASSWORD } from './helpers/lounge';

// Real mogis, raced by one browser per member on the official tracks. Nobody drives:
// mkDev.finishLap() (scripts/mk-dev.js) ends each player's laps, and each vote is cast with
// the Random button. The game runs ten times faster (mkDevTimeScale), so the countdown, the
// results screens and the track vote take a few seconds instead of a minute.
test.describe.configure({ mode: 'serial' });

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
	const context = await browser.newContext({ viewport: { width: 1000, height: 700 } });
	await context.addInitScript(() => { (window as any).mkDevTimeScale = 0.1; });
	const page = await context.newPage();
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
	const url = `http://127.0.0.1:8080/online.php?ranked&key=${queue.privgame_key}`;
	await Promise.all(pages.map(p => p.goto(url, { waitUntil: 'domcontentloaded' })));
	return { key: queue.privgame_key, players, pages };
}

async function waitForEvents(page, count: number, type: string, name?: string) {
	await page.waitForFunction(([count, type, name]) => (window as any).mkDevEvents.filter((e: any) =>
		e.type === type && (!name || e.name === name)).length >= count, [count, type, name] as const, { timeout: 60_000 });
}

async function voteRandom(page) {
	// the game sets the button's value as a property, which attribute selectors do not see
	await page.getByRole('button', { name: 'Random', exact: true }).click({ timeout: 60_000 });
}

// The server takes one more lap per update, so the player's kart completes its laps one at a
// time, each one counted before the next.
async function finishRace(page, race: number) {
	await waitForEvents(page, race, 'screen', 'race');
	const laps = await page.evaluate(() => (window as any).mkDevEval('oMap.tours'));
	for (let lap = 1; lap <= laps; lap++) {
		await expect.poll(() => page.evaluate((lap) => {
			if ((window as any).mkDevEval('oPlayers[0].tours') > lap)
				return true;
			try {
				(window as any).mkDev.finishLap();
			}
			catch (e) {
			}
			return false;
		}, lap), { timeout: 30_000, intervals: [500] }).toBe(true);
	}
	await waitForEvents(page, race, 'raceEnd');
}

// Players who walked out keep the next race waiting until the vote's deadline, 35 seconds after
// the last race: once everyone still in the room has voted, bring the deadline forward.
async function closeVote(key: number, voters: number[]) {
	await expect.poll(async () => {
		const [row]: any = await sql(`SELECT COUNT(*) AS n FROM mkjoueurs WHERE id IN (?) AND choice_map != 0`, [voters]);
		return Number(row.n);
	}, { timeout: 30_000 }).toBe(voters.length);
	await sql(`UPDATE mariokart SET time = UNIX_TIMESTAMP() WHERE link = ? AND map = -1`, [key]);
}

async function raceOnce(pages, race: number) {
	await Promise.all(pages.map(voteRandom));
	await Promise.all(pages.map(p => finishRace(p, race)));
}

test('a mogi voided at a race start sends the players still in the room to its results', async ({ browser }) => {
	test.setTimeout(120_000);
	const { key, players, pages } = await launchLineup(browser, 'mpvoid', 5);
	await raceOnce(pages, 1);

	// three of the five walk out, so the second race would need three bots
	for (const page of pages.slice(2))
		await page.context().close();
	const stayed = pages.slice(0, 2);
	await Promise.all(stayed.map(voteRandom));
	await closeVote(key, players.slice(0, 2));

	for (const page of stayed)
		await page.waitForURL('**/lounge.php?key=' + key, { timeout: 60_000 });
	const [match]: any = await sql(`SELECT cancelled_reason FROM mklounge_matches WHERE privgame_key = ?`, [key]);
	expect(match.cancelled_reason).toBe('too_many_bots');
	await expect(stayed[0].locator('body')).toContainText('too many players left');
});

// A member who comes back mid-mogi can watch the race their substitute is driving under their
// own id. That bot must stay on the track for them - not be taken for the spectator's own kart
// and hidden, shifting every name after it by one.
test('a member watching their own substitute sees every kart', async ({ browser }) => {
	test.setTimeout(120_000);
	const { key, players, pages } = await launchLineup(browser, 'mpspec', 4);
	await raceOnce(pages, 1);
	await pages[3].context().close();

	// nobody drives race 2, so it lasts as long as the member needs to walk in on it
	const racers = pages.slice(0, 3);
	await Promise.all(racers.map(voteRandom));
	await closeVote(key, players.slice(0, 3));
	await waitForEvents(pages[0], 2, 'screen', 'race');

	const watcher = await newPlayerPage(browser, 'mpspec', 4);
	await watcher.goto(pages[0].url(), { waitUntil: 'domcontentloaded' });
	// back mid-race, a member waits for the next one - unless they ask to watch this one
	await watcher.getByLabel('Spectator mode').check();
	await waitForEvents(watcher, 1, 'spectatorOn');
	await expect.poll(() => watcher.evaluate(() => (window as any).mkDev.karts().length), { timeout: 30_000 }).toBe(4);
	const karts = await watcher.evaluate(() => (window as any).mkDev.karts());
	expect(karts.map((k: any) => k.id).sort()).toEqual([...players].sort());
	// each tag on its own kart: the substitute keeps its member's name
	for (const kart of karts)
		expect(kart.nick).toBe(loungeBotName('mpspec', players.indexOf(kart.id) + 1));
});
