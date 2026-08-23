import { test, expect } from '@playwright/test';
import { sql } from './helpers/db';
import { LOUNGE_KEY_MIN, createLoungeBots, loungeBotPattern } from './helpers/lounge';

// lounge_tick() only runs for a logged-in caller, so the tick that finishes the match has
// to come from a real session.
async function login(page, pseudo = 'wargor', code = 'aaaa') {
	const res = await page.request.post('http://127.0.0.1:8080/api/testcode.php', { form: { pseudo, code } });
	expect(Number(await res.text())).toBeGreaterThan(0);
	for (const header of res.headersArray().filter(h => h.name.toLowerCase() === 'set-cookie')) {
		const [name, value] = header.value.split(';')[0].split('=');
		await page.context().addCookies([{ name, value, domain: '127.0.0.1', path: '/' }]);
	}
}

// A queue is staged as "launching" so lounge_tick(), which sweeps every launched queue
// regardless of who is polling, cannot finish a half-built match. Publishing is the last
// step, once the standings and race count are all in place.
async function publish(key: number) {
	await sql(`UPDATE mklounge_queues SET status = 'launched' WHERE privgame_key = ?`, [key]);
}

async function tick(page) {
	const res = await page.request.post('http://127.0.0.1:8080/api/lounge/tiers.php');
	const body = await res.json();
	expect(body.error).toBeUndefined();
}

// Drives a lounge match to completion and checks the rating pass. The match is staged
// directly in the database because playing 12 real races is not something an e2e test can
// do; everything from lounge_tick() onwards is the real code path.
//
// Serial, and in one file: lounge_tick() processes every launched queue, not just the
// caller's, so a tick fired by one of these tests finishes the others' matches too. Split
// across files they run in parallel workers and finish each other's half-staged matches.
test.describe.configure({ mode: 'serial' });

const PRIVGAME_KEY = LOUNGE_KEY_MIN;
const BOTS = loungeBotPattern('mmr');

test('a finished FFA match rates every player', async ({ page }) => {
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	expect(tier).toBeTruthy();

	// four fresh accounts, so everyone starts on the default rating
	const players = await createLoungeBots(4, 'mmr');

	const queue: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at)
		 VALUES (1, ?, 'launching', ?, NOW())`,
		[tier.id, PRIVGAME_KEY]
	);
	await sql(
		`INSERT INTO mklounge_matches (queue, season, tier, privgame_key, mode)
		 VALUES (?, 1, ?, ?, 'FFA')`,
		[queue.insertId, tier.id, PRIVGAME_KEY]
	);
	const [match]: any = await sql(`SELECT id FROM mklounge_matches WHERE privgame_key = ?`, [PRIVGAME_KEY]);

	// distinct cumulative scores, so the finishing order is unambiguous
	const scores = [120, 90, 60, 30];
	for (let i = 0; i < players.length; i++) {
		await sql(`INSERT INTO mklounge_match_players (\`match\`, player) VALUES (?, ?)`, [match.id, players[i]]);
		await sql(`INSERT INTO mkgamerank (game, player, pts) VALUES (?, ?, ?)`, [PRIVGAME_KEY, players[i], scores[i]]);
	}
	// past any plausible LOUNGE_RACES_PER_MATCH, so the tick finishes the match
	await sql(`INSERT INTO mkgamedata (game, aRaceCount, raceCount) VALUES (?, 999, 999)`, [PRIVGAME_KEY]);
	await publish(PRIVGAME_KEY);

	// lounge_tick() runs on this endpoint; it is what finishes the match and rates it
	await login(page);
	await tick(page);

	const rows: any[] = await sql(
		`SELECT j.nom, mp.final_position, mp.mmr_before, mp.mmr_after, mp.mmr_delta
		 FROM mklounge_match_players mp
		 JOIN mkjoueurs j ON j.id = mp.player
		 WHERE mp.\`match\` = ? ORDER BY mp.final_position`,
		[match.id]
	);
	expect(rows).toHaveLength(4);

	// everyone started level, so the pairwise result is symmetric around the middle
	expect(rows.map(r => r.final_position)).toEqual([1, 2, 3, 4]);
	expect(rows.map(r => Math.round(r.mmr_before))).toEqual([600, 600, 600, 600]);
	expect(rows.map(r => Math.round(r.mmr_delta))).toEqual([41, 14, -14, -41]);
	expect(rows.map(r => Math.round(r.mmr_after))).toEqual([641, 614, 586, 559]);

	// no rating is created or destroyed when nobody is against the floor
	const sum = rows.reduce((acc, r) => acc + Number(r.mmr_delta), 0);
	expect(Math.abs(sum)).toBeLessThan(1e-9);

	// the season record follows the match
	const season: any[] = await sql(
		`SELECT j.nom, p.mmr, p.peak_mmr, p.games, p.wins FROM mklounge_players p
		 JOIN mkjoueurs j ON j.id = p.player WHERE j.nom LIKE ? ORDER BY p.mmr DESC`,
		[BOTS]
	);
	expect(season).toHaveLength(4);
	expect(Math.round(season[0].mmr)).toBe(641);
	expect(Math.round(season[0].peak_mmr)).toBe(641);
	expect(season.map(r => r.games)).toEqual([1, 1, 1, 1]);
	expect(season.reduce((a, r) => a + r.wins, 0)).toBe(1);

	const [queueRow]: any = await sql(`SELECT status FROM mklounge_queues WHERE privgame_key = ?`, [PRIVGAME_KEY]);
	expect(queueRow.status).toBe('finished');
});

test('rating is idempotent when the tick runs again', async ({ page }) => {
	const before: any[] = await sql(
		`SELECT p.player, p.mmr, p.games FROM mklounge_players p
		 JOIN mkjoueurs j ON j.id = p.player WHERE j.nom LIKE ? ORDER BY p.player`,
		[BOTS]
	);
	expect(before.length).toBe(4);

	await login(page);
	await tick(page);

	const after: any[] = await sql(
		`SELECT p.player, p.mmr, p.games FROM mklounge_players p
		 JOIN mkjoueurs j ON j.id = p.player WHERE j.nom LIKE ? ORDER BY p.player`,
		[BOTS]
	);
	expect(after).toEqual(before);
});

test('the floor stops a rating going negative', async ({ page }) => {
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const key = PRIVGAME_KEY + 1;

	const ids: any[] = await sql(`SELECT id FROM mkjoueurs WHERE nom LIKE ? ORDER BY id`, [BOTS]);
	const players = ids.map(r => r.id);
	// park the eventual last-placed player just above zero
	await sql(`UPDATE mklounge_players SET mmr = 3 WHERE player = ?`, [players[3]]);

	const queue: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at)
		 VALUES (1, ?, 'launching', ?, NOW())`,
		[tier.id, key]
	);
	await sql(
		`INSERT INTO mklounge_matches (queue, season, tier, privgame_key, mode) VALUES (?, 1, ?, ?, 'FFA')`,
		[queue.insertId, tier.id, key]
	);
	const [match]: any = await sql(`SELECT id FROM mklounge_matches WHERE privgame_key = ?`, [key]);
	const scores = [120, 90, 60, 30];
	for (let i = 0; i < players.length; i++) {
		await sql(`INSERT INTO mklounge_match_players (\`match\`, player) VALUES (?, ?)`, [match.id, players[i]]);
		await sql(`INSERT INTO mkgamerank (game, player, pts) VALUES (?, ?, ?)`, [key, players[i], scores[i]]);
	}
	await sql(`INSERT INTO mkgamedata (game, aRaceCount, raceCount) VALUES (?, 999, 999)`, [key]);
	await publish(key);

	await login(page);
	await tick(page);

	const [last]: any = await sql(
		`SELECT mmr_before, mmr_after, mmr_delta FROM mklounge_match_players
		 WHERE \`match\` = ? AND player = ?`,
		[match.id, players[3]]
	);
	expect(Number(last.mmr_before)).toBeCloseTo(3, 6);
	expect(Number(last.mmr_after)).toBe(0);
	// clamped, so the loss is only what was left rather than the full computed drop
	expect(Number(last.mmr_delta)).toBeCloseTo(-3, 6);

	const [seasonRow]: any = await sql(`SELECT mmr FROM mklounge_players WHERE player = ?`, [players[3]]);
	expect(Number(seasonRow.mmr)).toBe(0);

});

// lounge_tick() runs on every poll from every player, so the code that finishes a match
// is genuinely reentrant in production. Without an atomic claim, two ticks both tally the
// standings and both apply the rating change, counting the match twice.

test('concurrent ticks finish a match exactly once', async ({ page, browser }) => {
	const KEY = LOUNGE_KEY_MIN + 10;

	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code='all'`);
	const players = await createLoungeBots(4, 'race');
	const q: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at) VALUES (1, ?, 'launching', ?, NOW())`,
		[tier.id, KEY]);
	await sql(`INSERT INTO mklounge_matches (queue, season, tier, privgame_key, mode) VALUES (?, 1, ?, ?, 'FFA')`,
		[q.insertId, tier.id, KEY]);
	const [m]: any = await sql(`SELECT id FROM mklounge_matches WHERE privgame_key = ?`, [KEY]);
	const scores = [120, 90, 60, 30];
	for (let i = 0; i < 4; i++) {
		await sql(`INSERT INTO mklounge_match_players (\`match\`, player) VALUES (?, ?)`, [m.id, players[i]]);
		await sql(`INSERT INTO mkgamerank (game, player, pts) VALUES (?, ?, ?)`, [KEY, players[i], scores[i]]);
	}
	await sql(`INSERT INTO mkgamedata (game, aRaceCount, raceCount) VALUES (?, 999, 999)`, [KEY]);
	await publish(KEY);

	// PHP serialises requests sharing a session, so each tick needs its own session to
	// actually race
	const contexts = await Promise.all(Array.from({ length: 6 }, () => browser.newContext()));
	await Promise.all(contexts.map(c =>
		c.request.post('http://127.0.0.1:8080/api/testcode.php', { form: { pseudo: 'wargor', code: 'aaaa' } })));
	await Promise.all(contexts.map(c =>
		c.request.post('http://127.0.0.1:8080/api/lounge/tiers.php')));
	await Promise.all(contexts.map(c => c.close()));

	const rows: any[] = await sql(
		`SELECT p.games, p.mmr FROM mklounge_players p JOIN mkjoueurs j ON j.id=p.player
		 WHERE j.nom LIKE ? ORDER BY p.mmr DESC`, [loungeBotPattern('race')]);
	expect(rows.map(r => r.games)).toEqual([1, 1, 1, 1]);
	expect(rows.map(r => Math.round(r.mmr))).toEqual([641, 614, 586, 559]);
});
