import { test, expect } from '@playwright/test';
import { sql } from './helpers/db';

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

async function tick(page) {
	const res = await page.request.post('http://127.0.0.1:8080/api/lounge/tiers.php');
	const body = await res.json();
	expect(body.error).toBeUndefined();
}

// Drives a lounge match to completion and checks the rating pass. The match is staged
// directly in the database because playing 12 real races is not something an e2e test can
// do; everything from lounge_tick() onwards is the real code path.
test.describe.configure({ mode: 'serial' });

const BOT_PREFIX = 'e2e-mmr-';
const PRIVGAME_KEY = 990001;

// Scoped by the bot-account name prefix rather than by run, so a crashed run is healed on
// the next one instead of leaving rows behind. Runs before as well as after: afterAll is
// skipped when a run is killed, which is exactly when leftovers appear.
async function cleanup() {
	const ids: any[] = await sql(`SELECT id FROM mkjoueurs WHERE nom LIKE ?`, [BOT_PREFIX + '%']);
	const playerIds = ids.map((r: any) => r.id);
	if (playerIds.length) {
		await sql(`DELETE FROM mklounge_match_players WHERE player IN (?)`, [playerIds]);
		await sql(`DELETE FROM mklounge_queue_members WHERE player IN (?)`, [playerIds]);
		await sql(`DELETE FROM mklounge_players WHERE player IN (?)`, [playerIds]);
		await sql(`DELETE FROM mkgamerank WHERE player IN (?)`, [playerIds]);
		await sql(`DELETE FROM mkjoueurs WHERE id IN (?)`, [playerIds]);
	}
	await sql(`DELETE FROM mklounge_matches WHERE privgame_key = ?`, [PRIVGAME_KEY]);
	await sql(`DELETE FROM mklounge_queues WHERE privgame_key = ?`, [PRIVGAME_KEY]);
	await sql(`DELETE FROM mkgamedata WHERE game = ?`, [PRIVGAME_KEY]);
	await sql(`DELETE FROM mkgamerank WHERE game = ?`, [PRIVGAME_KEY]);
}

test.beforeAll(cleanup);
test.afterAll(cleanup);

test('a finished FFA match rates every player', async ({ page }) => {
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	expect(tier).toBeTruthy();

	// four fresh accounts, so everyone starts on the default rating
	const players: number[] = [];
	for (let i = 1; i <= 4; i++) {
		const res: any = await sql(
			`INSERT INTO mkjoueurs
				(nom, course, code, joueur, choice_map, choice_rand, pts_vs, pts_battle, pts_challenge, online, deleted)
			 VALUES (?, 0, '', 'mario', 0, 0, 0, 0, 0, 0, 0)`,
			[BOT_PREFIX + i]
		);
		players.push(res.insertId);
	}

	const queue: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at)
		 VALUES (1, ?, 'launched', ?, NOW())`,
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
		[BOT_PREFIX + '%']
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
		[BOT_PREFIX + '%']
	);
	expect(before.length).toBe(4);

	await login(page);
	await tick(page);

	const after: any[] = await sql(
		`SELECT p.player, p.mmr, p.games FROM mklounge_players p
		 JOIN mkjoueurs j ON j.id = p.player WHERE j.nom LIKE ? ORDER BY p.player`,
		[BOT_PREFIX + '%']
	);
	expect(after).toEqual(before);
});

test('the floor stops a rating going negative', async ({ page }) => {
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const key = PRIVGAME_KEY + 1;
	await sql(`DELETE FROM mklounge_matches WHERE privgame_key = ?`, [key]);
	await sql(`DELETE FROM mklounge_queues WHERE privgame_key = ?`, [key]);
	await sql(`DELETE FROM mkgamedata WHERE game = ?`, [key]);
	await sql(`DELETE FROM mkgamerank WHERE game = ?`, [key]);

	const ids: any[] = await sql(`SELECT id FROM mkjoueurs WHERE nom LIKE ? ORDER BY id`, [BOT_PREFIX + '%']);
	const players = ids.map(r => r.id);
	// park the eventual last-placed player just above zero
	await sql(`UPDATE mklounge_players SET mmr = 3 WHERE player = ?`, [players[3]]);

	const queue: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at)
		 VALUES (1, ?, 'launched', ?, NOW())`,
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

	await sql(`DELETE FROM mklounge_match_players WHERE \`match\` = ?`, [match.id]);
	await sql(`DELETE FROM mklounge_matches WHERE privgame_key = ?`, [key]);
	await sql(`DELETE FROM mklounge_queues WHERE privgame_key = ?`, [key]);
	await sql(`DELETE FROM mkgamedata WHERE game = ?`, [key]);
	await sql(`DELETE FROM mkgamerank WHERE game = ?`, [key]);
});
