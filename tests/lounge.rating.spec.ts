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

// Stages a launched mogi that is past its join window, with `joined` of its bots actually
// in the room. Returns the queue id, the match id and the full bot list.
async function stageLaunchedMatch(tag: string, key: number, bots: number, joined: number, races: number) {
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code='all'`);
	const players = await createLoungeBots(bots, tag);
	const q: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at)
		 VALUES (1, ?, 'launching', ?, NOW() - INTERVAL 10 MINUTE)`, [tier.id, key]);
	await sql(`INSERT INTO mklounge_matches (queue, season, tier, privgame_key, mode) VALUES (?, 1, ?, ?, 'FFA')`,
		[q.insertId, tier.id, key]);
	for (const player of players)
		await sql(`INSERT INTO mklounge_queue_members (queue, player) VALUES (?, ?)`, [q.insertId, player]);
	const [m]: any = await sql(`SELECT id FROM mklounge_matches WHERE privgame_key = ?`, [key]);
	for (const player of players)
		await sql(`INSERT INTO mklounge_match_players (\`match\`, player) VALUES (?, ?)`, [m.id, player]);
	await sql(`INSERT INTO mkgameoptions (id, rules, public) VALUES (?, ?, 0)`,
		[key, JSON.stringify({ minPlayers: bots, maxPlayers: bots, raceLimit: 12, lounge: 1 })]);
	if (races)
		await sql(`INSERT INTO mkgamedata (game, aRaceCount, raceCount) VALUES (?, ?, ?)`, [key, races, races]);
	// the room only exists while the mogi is alive; `mariokart` is a MEMORY table
	if (joined) {
		const room: any = await sql(`INSERT INTO mariokart (map, time, cup, mode, link) VALUES (-1, ?, 0, 0, ?)`,
			[Math.floor(Date.now() / 1000), key]);
		for (const player of players.slice(0, joined))
			await sql(`INSERT INTO mkplayers (id, course, team, finaltime, finalts) VALUES (?, ?, -1, 0, 0)`,
				[player, room.insertId]);
	}
	await publish(key);
	return { queueId: q.insertId, matchId: m.id, players };
}

async function rulesOf(key: number) {
	const [row]: any = await sql(`SELECT rules FROM mkgameoptions WHERE id = ?`, [key]);
	return JSON.parse(row.rules);
}

// Rule 4da penalises the player who did not turn up, not the rest of the lineup. Before this,
// minPlayers was pinned to the lineup size, so one absentee left everyone else stuck on
// "waiting for players" and the whole mogi was voided.
test('a partial lineup still plays, and only the absentee is struck', async ({ page }) => {
	await login(page);
	const key = LOUNGE_KEY_MIN + 20;
	const { queueId, players } = await stageLaunchedMatch('noshow', key, 4, 3, 0);

	await tick(page);

	const [queue]: any = await sql(`SELECT status FROM mklounge_queues WHERE id = ?`, [queueId]);
	expect(queue.status).toBe('launched');
	// the room now only needs the three who are in it, and a bot stands in for the fourth
	// so the field - and the point distribution built for it - keeps its size
	const rules = await rulesOf(key);
	expect(rules.minPlayers).toBe(3);
	expect(rules.cpu).toBe(1);

	const absentee = players[3];
	const [struck]: any = await sql(`SELECT strikes FROM mklounge_players WHERE player = ?`, [absentee]);
	expect(struck.strikes).toBe(1);
	const active: any = await sql(
		`SELECT player FROM mklounge_queue_members WHERE queue = ? AND dropped_at IS NULL`, [queueId]);
	expect(active.map((r: any) => r.player).sort()).toEqual(players.slice(0, 3).sort());
});

// "si un joueur est déconnecté durant la partie [...] il est remplacé par un bot et le
// joueur reçoit un strike" - the mogi carries on without the player who walked out.
test('a player who walks out mid-mogi is struck and replaced', async ({ page }) => {
	await login(page);
	const key = LOUNGE_KEY_MIN + 23;
	const { queueId, players } = await stageLaunchedMatch('walkout', key, 4, 4, 3);

	// one of them leaves the room three races in
	await sql(`DELETE FROM mkplayers WHERE id = ?`, [players[3]]);
	await tick(page);

	const [queue]: any = await sql(`SELECT status FROM mklounge_queues WHERE id = ?`, [queueId]);
	expect(queue.status).toBe('launched');

	const rules = await rulesOf(key);
	expect(rules.minPlayers).toBe(3);
	expect(rules.cpu).toBe(1);

	const [struck]: any = await sql(`SELECT strikes FROM mklounge_players WHERE player = ?`, [players[3]]);
	expect(struck.strikes).toBe(1);
	const [row]: any = await sql(
		`SELECT strike_reason FROM mklounge_match_players mp
		 JOIN mklounge_matches m ON m.id = mp.\`match\`
		 WHERE m.privgame_key = ? AND mp.player = ?`, [key, players[3]]);
	expect(row.strike_reason).toBe('disconnect');

	// and running again does not strike them twice
	await tick(page);
	const [again]: any = await sql(`SELECT strikes FROM mklounge_players WHERE player = ?`, [players[3]]);
	expect(again.strikes).toBe(1);
});

test('a lineup too small to race is voided instead', async ({ page }) => {
	await login(page);
	const key = LOUNGE_KEY_MIN + 21;
	const { queueId } = await stageLaunchedMatch('small', key, 4, 1, 0);

	await tick(page);

	const [queue]: any = await sql(`SELECT status FROM mklounge_queues WHERE id = ?`, [queueId]);
	expect(queue.status).toBe('cancelled');
	const [match]: any = await sql(`SELECT cancelled_reason FROM mklounge_matches WHERE queue = ?`, [queueId]);
	expect(match.cancelled_reason).toBe('no_show');
});

// Without this a mogi that dies part-way stays "launched" for ever, and every member of the
// lineup is permanently "already queued" with no way out - leave.php refuses anything that
// is not an open queue.
test('an abandoned mogi releases its lineup instead of stranding it', async ({ page }) => {
	await login(page);
	const key = LOUNGE_KEY_MIN + 22;
	// five races in, and the room is gone: nobody is playing this any more
	const { queueId, players } = await stageLaunchedMatch('aband', key, 4, 0, 5);

	await tick(page);

	const [queue]: any = await sql(`SELECT status FROM mklounge_queues WHERE id = ?`, [queueId]);
	expect(queue.status).toBe('cancelled');
	const [match]: any = await sql(`SELECT cancelled_reason FROM mklounge_matches WHERE queue = ?`, [queueId]);
	expect(match.cancelled_reason).toBe('abandoned');

	const stranded: any = await sql(
		`SELECT player FROM mklounge_queue_members WHERE queue = ? AND dropped_at IS NULL`, [queueId]);
	expect(stranded).toHaveLength(0);

	// a voided mogi rates nobody
	const rated: any = await sql(
		`SELECT games FROM mklounge_players WHERE player IN (?) AND games > 0`, [players]);
	expect(rated).toHaveLength(0);
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
