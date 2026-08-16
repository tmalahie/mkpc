import { test, expect } from '@playwright/test';
import { sql } from './helpers/db';

// lounge_tick() runs on every poll from every player, so the code that finishes a match
// is genuinely reentrant in production. Without an atomic claim, two ticks both tally the
// standings and both apply the rating change, counting the match twice.

test('concurrent ticks finish a match exactly once', async ({ page, browser }) => {
	const KEY = 991234;
	const P = 'e2e-race-';
	const wipe = async () => {
		const ids: any[] = await sql(`SELECT id FROM mkjoueurs WHERE nom LIKE ?`, [P + '%']);
		const pid = ids.map((r: any) => r.id);
		if (pid.length) {
			await sql(`DELETE FROM mklounge_match_players WHERE player IN (?)`, [pid]);
			await sql(`DELETE FROM mklounge_players WHERE player IN (?)`, [pid]);
			await sql(`DELETE FROM mkgamerank WHERE player IN (?)`, [pid]);
			await sql(`DELETE FROM mkjoueurs WHERE id IN (?)`, [pid]);
		}
		await sql(`DELETE FROM mklounge_matches WHERE privgame_key = ?`, [KEY]);
		await sql(`DELETE FROM mklounge_queues WHERE privgame_key = ?`, [KEY]);
		await sql(`DELETE FROM mkgamedata WHERE game = ?`, [KEY]);
	};
	await wipe();

	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code='all'`);
	const players: number[] = [];
	for (let i = 1; i <= 4; i++) {
		const r: any = await sql(
			`INSERT INTO mkjoueurs (nom, course, code, joueur, choice_map, choice_rand, pts_vs, pts_battle, pts_challenge, online, deleted)
			 VALUES (?, 0, '', 'mario', 0,0,0,0,0,0,0)`, [P + i]);
		players.push(r.insertId);
	}
	const q: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at) VALUES (1, ?, 'launched', ?, NOW())`,
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
		 WHERE j.nom LIKE ? ORDER BY p.mmr DESC`, [P + '%']);
	expect(rows.map(r => r.games)).toEqual([1, 1, 1, 1]);
	expect(rows.map(r => Math.round(r.mmr))).toEqual([641, 614, 586, 559]);
	await wipe();
});
