import { test, expect } from '@playwright/test';
import { sql } from './helpers/db';
import { LOUNGE_KEY_MIN, createLoungeBots, loungeBotName, loungeBotPattern, LOUNGE_BOT_PASSWORD } from './helpers/lounge';

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

// "si tu as rate plus de 4 courses tu prends -25, si tu as joue au moins 8 courses mais rate
// au moins une tu prends -10". The bot races in the absentee's place, so they are rated on the
// result it produced and the absence is charged on top of it - rather than going unrated,
// which would pay better than turning up.
test('an absence is charged on top of the rating the bot earned', async ({ page }) => {
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const key = LOUNGE_KEY_MIN + 24;
	const players = await createLoungeBots(4, 'absent');

	const queue: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at)
		 VALUES (1, ?, 'launching', ?, NOW())`, [tier.id, key]);
	await sql(`INSERT INTO mklounge_matches (queue, season, tier, privgame_key, mode)
	           VALUES (?, 1, ?, ?, 'FFA')`, [queue.insertId, tier.id, key]);
	const [match]: any = await sql(`SELECT id FROM mklounge_matches WHERE privgame_key = ?`, [key]);

	// same finishing order as the plain FFA match above, so the deltas are the same 41/14/-14/-41
	// and the only difference on the board is what the absences cost
	const scores = [120, 90, 60, 30];
	const attendance = [12, 12, 11, 3];
	for (let i = 0; i < players.length; i++) {
		await sql(`INSERT INTO mklounge_match_players (\`match\`, player, races_played) VALUES (?, ?, ?)`,
			[match.id, players[i], attendance[i]]);
		await sql(`INSERT INTO mkgamerank (game, player, pts) VALUES (?, ?, ?)`, [key, players[i], scores[i]]);
	}
	await sql(`INSERT INTO mkgamedata (game, aRaceCount, raceCount) VALUES (?, 999, 999)`, [key]);
	await publish(key);

	await login(page);
	await tick(page);

	const rows: any[] = await sql(
		`SELECT mp.races_played, mp.mmr_penalty, mp.mmr_delta FROM mklounge_match_players mp
		 WHERE mp.\`match\` = ? ORDER BY mp.final_position`, [match.id]);
	expect(rows.map(r => r.races_played)).toEqual([12, 12, 11, 3]);
	// nothing for the two who raced it all, -10 for one missed race, -25 for missing a third of it
	expect(rows.map(r => r.mmr_penalty === null ? null : Math.round(r.mmr_penalty))).toEqual([0, 0, -10, -25]);
	expect(rows.map(r => Math.round(r.mmr_delta))).toEqual([41, 14, -24, -66]);
});

// The rule is a flat count - "plus de 4 courses ratees" - not a share of the mogi. Staff
// shorten races_per_match to test, and a third of a two-race mogi is one race: reading the
// threshold as a proportion charged the heavy penalty for missing a single race.
test('a short mogi charges the light penalty for one missed race', async ({ page }) => {
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const key = LOUNGE_KEY_MIN + 25;
	const players = await createLoungeBots(4, 'shortmogi');
	// restored whatever happens: every other case in this file reads the default mogi length,
	// and a leaked override would rate them against the wrong one
	await sql(`INSERT INTO mklounge_settings (name, value) VALUES ('races_per_match', 2)
	           ON DUPLICATE KEY UPDATE value = VALUES(value)`);
	try {
	const queue: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at)
		 VALUES (1, ?, 'launching', ?, NOW())`, [tier.id, key]);
	await sql(`INSERT INTO mklounge_matches (queue, season, tier, privgame_key, mode)
	           VALUES (?, 1, ?, ?, 'FFA')`, [queue.insertId, tier.id, key]);
	const [match]: any = await sql(`SELECT id FROM mklounge_matches WHERE privgame_key = ?`, [key]);

	const scores = [120, 90, 60, 30];
	const attendance = [2, 2, 1, 0];
	for (let i = 0; i < players.length; i++) {
		await sql(`INSERT INTO mklounge_match_players (\`match\`, player, races_played) VALUES (?, ?, ?)`,
			[match.id, players[i], attendance[i]]);
		await sql(`INSERT INTO mkgamerank (game, player, pts) VALUES (?, ?, ?)`, [key, players[i], scores[i]]);
	}
	await sql(`INSERT INTO mkgamedata (game, aRaceCount, raceCount) VALUES (?, 999, 999)`, [key]);
	await publish(key);

	await login(page);
	await tick(page);

	const rows: any[] = await sql(
		`SELECT mp.races_played, mp.mmr_penalty FROM mklounge_match_players mp
		 WHERE mp.\`match\` = ? ORDER BY mp.final_position`, [match.id]);
	expect(rows.map(r => r.races_played)).toEqual([2, 2, 1, 0]);
	// missing one of two races is still only one missed race, so it is the light penalty;
	// missing both is still under the threshold and stays light too
	expect(rows.map(r => Math.round(r.mmr_penalty))).toEqual([0, 0, -10, -10]);
	}
	finally {
		await sql(`DELETE FROM mklounge_settings WHERE name = 'races_per_match'`);
	}
});

// If the race-end hook never ran, every attendance is 0 - which must read as "we do not know
// how long the mogi was", not as "nobody turned up for any of it".
test('a mogi with no attendance recorded penalises nobody', async ({ page }) => {
	const [row]: any = await sql(
		`SELECT mmr_penalty FROM mklounge_match_players WHERE \`match\` =
		 (SELECT id FROM mklounge_matches WHERE privgame_key = ?) LIMIT 1`, [PRIVGAME_KEY]);
	expect(Math.round(row.mmr_penalty)).toBe(0);
});

// Two bots at most: a lineup of four still races with two of them in the room.
test('a lineup two players short still plays', async ({ page }) => {
	await login(page);
	const key = LOUNGE_KEY_MIN + 26;
	const { queueId } = await stageLaunchedMatch('twoshort', key, 4, 2, 0);

	await tick(page);

	const [queue]: any = await sql(`SELECT status FROM mklounge_queues WHERE id = ?`, [queueId]);
	expect(queue.status).toBe('launched');
	expect((await rulesOf(key)).minPlayers).toBe(2);
});

// One more absentee and it is voided - and voiding it costs the absentees more than an
// absence would have, since the rest of the lineup came for nothing. Nobody else is rated.
test('a lineup too small to race is voided instead, at the absentees\' expense', async ({ page }) => {
	await login(page);
	const key = LOUNGE_KEY_MIN + 21;
	const { queueId, matchId, players } = await stageLaunchedMatch('small', key, 4, 1, 0);

	await tick(page);

	const [queue]: any = await sql(`SELECT status FROM mklounge_queues WHERE id = ?`, [queueId]);
	expect(queue.status).toBe('cancelled');
	const [match]: any = await sql(`SELECT cancelled_reason FROM mklounge_matches WHERE queue = ?`, [queueId]);
	expect(match.cancelled_reason).toBe('no_show');

	const rows: any = await sql(
		`SELECT mp.player, mp.mmr_before, mp.mmr_after, mp.mmr_penalty, p.mmr
		 FROM mklounge_match_players mp JOIN mklounge_players p ON p.player = mp.player AND p.season = 1
		 WHERE mp.\`match\` = ? ORDER BY mp.player`, [matchId]);
	const present = rows.find((r: any) => r.player === players[0]);
	expect(present.mmr_after).toBeNull();
	expect(Number(present.mmr)).toBe(600);
	for (const absent of rows.filter((r: any) => r.player !== players[0])) {
		expect(Number(absent.mmr_penalty)).toBe(-50);
		expect(Number(absent.mmr_after)).toBe(Number(absent.mmr_before) - 50);
		expect(Number(absent.mmr)).toBe(Number(absent.mmr_after));
	}
});

// Staff fix a rating the way Lorenzi lets them: a player's starting rating, or a compensation
// at one mogi - and every mogi played since is rated again from there, the other players'
// moves included.
test('editing a table recalculates every mogi played since', async ({ page }) => {
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const [newcomer, rival] = await createLoungeBots(2, 'edit');
	const rateMogi = async (key: number, scores: number[]) => {
		const q: any = await sql(
			`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at)
			 VALUES (1, ?, 'launching', ?, NOW())`, [tier.id, key]);
		await sql(`INSERT INTO mklounge_matches (queue, season, tier, privgame_key, mode) VALUES (?, 1, ?, ?, 'FFA')`,
			[q.insertId, tier.id, key]);
		const [m]: any = await sql(`SELECT id FROM mklounge_matches WHERE privgame_key = ?`, [key]);
		for (let i = 0; i < 2; i++) {
			await sql(`INSERT INTO mklounge_match_players (\`match\`, player) VALUES (?, ?)`, [m.id, [newcomer, rival][i]]);
			await sql(`INSERT INTO mkgamerank (game, player, pts) VALUES (?, ?, ?)`, [key, [newcomer, rival][i], scores[i]]);
		}
		await sql(`INSERT INTO mkgamedata (game, aRaceCount, raceCount) VALUES (?, 999, 999)`, [key]);
		await publish(key);
		await tick(page);
		return m.id;
	};
	const row = async (matchId: number, player: number) => {
		const [r]: any = await sql(
			`SELECT mmr_before, mmr_after, mmr_delta, mmr_adjust FROM mklounge_match_players
			 WHERE \`match\` = ? AND player = ?`, [matchId, player]);
		return { before: Number(r.mmr_before), after: Number(r.mmr_after), delta: Number(r.mmr_delta), adjust: r.mmr_adjust };
	};
	const rating = async (player: number) => {
		const [r]: any = await sql(`SELECT mmr FROM mklounge_players WHERE player = ? AND season = 1`, [player]);
		return Number(r.mmr);
	};

	await login(page);
	const first = await rateMogi(LOUNGE_KEY_MIN + 27, [90, 30]);
	const second = await rateMogi(LOUNGE_KEY_MIN + 28, [30, 90]);
	const rivalSecondBefore = await row(second, rival);
	expect((await row(first, newcomer)).before).toBe(600);

	const api = async (request: any, form: Record<string, string>) => (await request.post(
		'http://127.0.0.1:8080/api/lounge/edit-match.php', { form })).json();
	const { text } = await api(page.request, { action: 'load', match: String(first) });
	// the table comes back as the text staff paste on Lorenzi
	expect(text).toBe(loungeBotName('edit', 1) + ' 90\n' + loungeBotName('edit', 2) + ' 30');
	const edit = (form: Record<string, string>, action = 'save') =>
		api(page.request, { action, match: String(first), text, ...form });

	// a preview shows the outcome and writes nothing
	const previewed = await edit({ ['placement_' + newcomer]: '800' }, 'preview');
	expect(previewed.match.players.find((p: any) => p.id === newcomer).mmr_before).toBe(800);
	expect((await row(first, newcomer)).before).toBe(600);

	// placed at 800 rather than the default 600
	expect((await edit({ ['placement_' + newcomer]: '800' })).saved).toBe(true);
	const firstAfter = await row(first, newcomer);
	expect(firstAfter.before).toBe(800);
	const secondAfter = await row(second, newcomer);
	expect(secondAfter.before).toBeCloseTo(firstAfter.after, 6);
	expect(await rating(newcomer)).toBeCloseTo(secondAfter.after, 6);
	// a higher-rated opponent beaten is worth more to the rival in the second mogi
	expect((await row(second, rival)).delta).toBeGreaterThan(rivalSecondBefore.delta);

	// a compensation lands on top of what the mogi gave, and carries on into the next one
	const rivalBefore = await rating(rival);
	await edit({ ['adjust_' + rival]: '20' });
	expect(Number((await row(first, rival)).adjust)).toBe(20);
	expect((await row(second, rival)).before).toBeCloseTo((await row(first, rival)).after, 6);
	expect(await rating(rival)).toBeGreaterThan(rivalBefore);

	// the scores themselves can be corrected: the rival won the first mogi after all
	await api(page.request, { action: 'save', match: String(first),
		text: loungeBotName('edit', 1) + ' 30\n' + loungeBotName('edit', 2) + ' 40|50' });
	const [fixed]: any = await sql(
		`SELECT final_score, final_position, gp_scores FROM mklounge_match_players WHERE \`match\` = ? AND player = ?`,
		[first, rival]);
	expect(fixed).toEqual({ final_score: 90, final_position: 1, gp_scores: '40|50' });
	expect((await row(first, rival)).delta).toBeGreaterThan(0);

	// a table played elsewhere - a Discord tournament - is added and rated like any other
	const games = async (player: number) =>
		Number((await sql(`SELECT games FROM mklounge_players WHERE player = ? AND season = 1`, [player]) as any)[0].games);
	const beforeTable = await rating(newcomer);
	const created = await api(page.request, { action: 'save', tier: String(tier.id),
		text: 'A\n' + loungeBotName('edit', 1) + ' [fr] 50+12|40\n\nB\n' + loungeBotName('edit', 2) + ' 20|30' });
	expect(created.saved).toBe(true);
	expect(created.match.manual).toBe(true);
	expect(created.match.players.find((p: any) => p.id === newcomer).score).toBe(102);
	expect(await games(newcomer)).toBe(3);
	expect(await rating(newcomer)).toBeGreaterThan(beforeTable);

	// and deleting it takes it out of everyone's history
	expect((await api(page.request, { action: 'delete', match: String(created.match.id) })).deleted).toBe(true);
	expect(await games(newcomer)).toBe(2);
	expect(await rating(newcomer)).toBeCloseTo(beforeTable, 6);

	// a table that does not add up is refused, line by line
	const refusedTable = await api(page.request, { action: 'preview', tier: String(tier.id),
		text: 'A\nNobodyCalledThis 10\n' + loungeBotName('edit', 1) + ' 1o\nPenalty -10' });
	expect(refusedTable.errors.map((e: any) => e.slice(0, 2))).toEqual([[2, 'unknown_player'], [3, 'no_score'], [4, 'team_penalty']]);

	// recorded for the other moderators
	for (const log of ['LoungeMatchEdit ' + first, 'LoungeMatchCreate ' + created.match.id, 'LoungeMatchDelete ' + created.match.id]) {
		const [logged]: any = await sql(`SELECT id FROM mklogs WHERE log = ?`, [log]);
		expect(logged).toBeTruthy();
		await sql(`DELETE FROM mklogsnapshots WHERE log = ?`, [logged.id]);
		await sql(`DELETE FROM mklogs WHERE id = ?`, [logged.id]);
	}

	// and it is only theirs to do
	await createLoungeBots(1, 'editguest');
	const guest = await page.context().browser()!.newContext();
	const guestPage = await guest.newPage();
	await login(guestPage, loungeBotName('editguest', 1), LOUNGE_BOT_PASSWORD);
	const refused = await api(guestPage.request, { action: 'save', match: String(first), text, ['adjust_' + rival]: '500' });
	expect(refused.error).toBe('forbidden');
	await guest.close();
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

// The ladder place either side of a mogi. Absolute places depend on the whole season's
// table, so what is asserted is the relation: the same order the ratings are in, and a move
// in the direction the rating went.
test('a rated mogi records where it left every player on the ladder', async ({ page }) => {
	const [tier]: any = await sql(`SELECT id FROM mklounge_tiers WHERE code = 'all'`);
	const key = LOUNGE_KEY_MIN + 9;
	const players = await createLoungeBots(4, 'places');
	// The top two sit close together on purpose. Every player who beats a higher-rated
	// opponent gains, so in an FFA only the one who comes last can actually fall - and it can
	// only fall past somebody if there is somebody just below it.
	const ratings = [900, 930, 1150, 1160];
	for (let i = 0; i < players.length; i++)
		await sql(
			`INSERT INTO mklounge_players (player, season, mmr, peak_mmr, games) VALUES (?, 1, ?, ?, 4)
			 ON DUPLICATE KEY UPDATE mmr = VALUES(mmr), peak_mmr = VALUES(peak_mmr), games = VALUES(games)`,
			[players[i], ratings[i], ratings[i]]);

	const queue: any = await sql(
		`INSERT INTO mklounge_queues (season, tier, status, privgame_key, launched_at)
		 VALUES (1, ?, 'launching', ?, NOW())`, [tier.id, key]);
	await sql(`INSERT INTO mklounge_matches (queue, season, tier, privgame_key, mode) VALUES (?, 1, ?, ?, 'FFA')`,
		[queue.insertId, tier.id, key]);
	const [match]: any = await sql(`SELECT id FROM mklounge_matches WHERE privgame_key = ?`, [key]);
	// the lowest rated player wins it, which is the biggest climb the table can show
	const scores = [120, 90, 60, 30];
	for (let i = 0; i < players.length; i++) {
		await sql(`INSERT INTO mklounge_match_players (\`match\`, player) VALUES (?, ?)`, [match.id, players[i]]);
		await sql(`INSERT INTO mkgamerank (game, player, pts) VALUES (?, ?, ?)`, [key, players[i], scores[i]]);
	}
	await sql(`INSERT INTO mkgamedata (game, aRaceCount, raceCount) VALUES (?, 999, 999)`, [key]);
	await publish(key);

	await login(page);
	await tick(page);

	const rows: any[] = await sql(
		`SELECT mp.place_before, mp.place_after, mp.mmr_before, mp.mmr_after
		 FROM mklounge_match_players mp WHERE mp.\`match\` = ? ORDER BY mp.mmr_after DESC`,
		[match.id]);
	expect(rows).toHaveLength(4);
	for (const row of rows) {
		expect(row.place_before).toBeGreaterThan(0);
		expect(row.place_after).toBeGreaterThan(0);
	}
	// ordered by rating, the places come out ordered too
	const places = rows.map(r => r.place_after);
	expect(places).toEqual([...places].sort((a, b) => a - b));
	// the player who came last dropped behind the one they started just ahead of, and the
	// winner gave up no ground
	const [last]: any = await sql(
		`SELECT place_before, place_after FROM mklounge_match_players
		 WHERE \`match\` = ? AND player = ?`, [match.id, players[3]]);
	expect(last.place_after).toBeGreaterThan(last.place_before);
	const [winner]: any = await sql(
		`SELECT place_before, place_after FROM mklounge_match_players
		 WHERE \`match\` = ? AND player = ?`, [match.id, players[0]]);
	expect(winner.place_after).toBeLessThanOrEqual(winner.place_before);
});

// The points a single race was worth, which nothing recorded before: `mkmatches` held the
// finishing position and nothing else, so a mogi's score could only ever be read as one
// number. reload.php is the only place that knows them, so the race is driven through it.
test('a finished race records what it was worth, and which game it belonged to', async ({ page }) => {
	const key = LOUNGE_KEY_MIN + 11;
	const players = await createLoungeBots(2, 'racepts');
	// a custom game with a point distribution of its own, the shape a lounge room uses
	await sql(
		`INSERT INTO mkgameoptions (id, rules, public) VALUES (?, ?, 0)`,
		[key, JSON.stringify({
			friendly: 1, localScore: 1, minPlayers: 2, maxPlayers: 2, raceLimit: 12,
			ptDistrib: { value: [10, 4], name: '2p' },
		})]
	);
	// the race counter a game carries, created when it starts and bumped as each race ends
	await sql(`INSERT INTO mkgamedata (game, aRaceCount, raceCount) VALUES (?, 0, 0)`, [key]);
	const room: any = await sql(
		`INSERT INTO mariokart (map, time, cup, mode, link) VALUES (1, ?, 0, 0, ?)`,
		[Math.floor(Date.now() / 1000), key]
	);
	// both karts over the line, so the race is finished and reload.php scores it
	for (let i = 0; i < players.length; i++)
		await sql(
			`INSERT INTO mkplayers (id, course, team, controller, tours, place, aPts, connecte, finaltime, finalts)
			 VALUES (?, ?, -1, 0, 4, ?, ?, 0, 0, 0)`,
			[players[i], room.insertId, i + 1, i === 0 ? 25 : 8]
		);

	// reload.php reads the caller's room off their own account
	await login(page, loungeBotName('racepts', 1), LOUNGE_BOT_PASSWORD);
	await sql(`UPDATE mkjoueurs SET course = ? WHERE id = ?`, [room.insertId, players[0]]);
	const res = await page.request.post('http://127.0.0.1:8080/api/reload.php', {
		data: { laps: 3 },
	});
	expect(res.ok()).toBeTruthy();

	const rows: any[] = await sql(
		`SELECT player, \`rank\`, link, race, pts_before, pts_inc FROM mkmatches
		 WHERE link = ? AND player IN (?) ORDER BY \`rank\``, [key, players]);
	expect(rows).toHaveLength(2);
	// the distribution decided the points, the room's running totals were the before
	expect(rows.map(r => r.pts_inc)).toEqual([10, 4]);
	expect(rows.map(r => r.pts_before)).toEqual([25, 8]);
	expect(rows.map(r => r.player)).toEqual(players);
	// the link, not the room: rooms live in a MEMORY table and this one is already gone
	expect(rows.every(r => r.link === key)).toBeTruthy();
	// and which race of the mogi it was, so the points can be banded without counting rows
	expect(rows.map(r => r.race)).toEqual([1, 1]);

	// and the running total the mogi is scored on moved by exactly that much
	const scores: any[] = await sql(
		`SELECT player, pts FROM mkgamerank WHERE game = ? ORDER BY pts DESC`, [key]);
	expect(scores.map(s => s.pts)).toEqual([35, 12]);

	await sql(`DELETE FROM mkplayers WHERE course = ?`, [room.insertId]);
	await sql(`DELETE FROM mariokart WHERE id = ?`, [room.insertId]);
	await sql(`UPDATE mkjoueurs SET course = 0 WHERE id = ?`, [players[0]]);
});

// The race end is the lounge's only heartbeat during a mogi, and the moment a player is
// counted as having played - so the log records which race it was and who was still there.
test('the end of a lounge race is in the event log, with who was still racing', async ({ page }) => {
	const key = LOUNGE_KEY_MIN + 12;
	const players = await createLoungeBots(2, 'raceend');
	await sql(
		`INSERT INTO mkgameoptions (id, rules, public) VALUES (?, ?, 0)`,
		[key, JSON.stringify({
			friendly: 1, localScore: 1, minPlayers: 2, maxPlayers: 2, raceLimit: 12, lounge: 1,
			ptDistrib: { value: [10, 4], name: '2p' },
		})]
	);
	await sql(`INSERT INTO mkgamedata (game, aRaceCount, raceCount) VALUES (?, 0, 0)`, [key]);
	const room: any = await sql(
		`INSERT INTO mariokart (map, time, cup, mode, link) VALUES (1, ?, 0, 0, ?)`,
		[Math.floor(Date.now() / 1000), key]
	);
	for (let i = 0; i < players.length; i++)
		await sql(
			`INSERT INTO mkplayers (id, course, team, controller, tours, place, aPts, connecte, finaltime, finalts)
			 VALUES (?, ?, -1, 0, 4, ?, 0, 0, 0, 0)`,
			[players[i], room.insertId, i + 1]
		);

	await login(page, loungeBotName('raceend', 1), LOUNGE_BOT_PASSWORD);
	await sql(`UPDATE mkjoueurs SET course = ? WHERE id = ?`, [room.insertId, players[0]]);
	const [{ since }]: any = await sql(`SELECT IFNULL(MAX(id), 0) AS since FROM mklounge_events`);
	const res = await page.request.post('http://127.0.0.1:8080/api/reload.php', { data: { laps: 3 } });
	expect(res.ok()).toBeTruthy();

	const events: any[] = await sql(
		`SELECT source, actor, data FROM mklounge_events
		 WHERE id > ? AND privgame_key = ? AND event = 'race_finished'`, [since, key]);
	expect(events).toHaveLength(1);
	expect(events[0].source).toBe('reload.php');
	expect(events[0].actor).toBe(players[0]);
	const data = JSON.parse(events[0].data);
	expect(data).toMatchObject({ race: 1, course: room.insertId });
	expect([...data.present].sort()).toEqual([...players].sort());

	await sql(`DELETE FROM mkplayers WHERE course = ?`, [room.insertId]);
	await sql(`DELETE FROM mariokart WHERE id = ?`, [room.insertId]);
	await sql(`UPDATE mkjoueurs SET course = 0 WHERE id = ?`, [players[0]]);
});

// The other half of it: a public race, where the points are the player's own VS total rather
// than a room's running score. Same two columns, and no link, because there is no link.
test('a public race records the points it moved, against no game in particular', async ({ page }) => {
	const players = await createLoungeBots(2, 'pubpts');
	const before = [10000, 8000];
	const room: any = await sql(
		`INSERT INTO mariokart (map, time, cup, mode, link) VALUES (1, ?, 0, 0, 0)`,
		[Math.floor(Date.now() / 1000)]
	);
	for (let i = 0; i < players.length; i++) {
		// the optimistic update only lands when the two agree, so they are set together
		await sql(`UPDATE mkjoueurs SET pts_vs = ? WHERE id = ?`, [before[i], players[i]]);
		await sql(
			`INSERT INTO mkplayers (id, course, team, controller, tours, place, aPts, connecte, finaltime, finalts)
			 VALUES (?, ?, -1, 0, 4, ?, ?, 0, 0, 0)`,
			[players[i], room.insertId, i + 1, before[i]]
		);
	}

	await login(page, loungeBotName('pubpts', 1), LOUNGE_BOT_PASSWORD);
	await sql(`UPDATE mkjoueurs SET course = ? WHERE id = ?`, [room.insertId, players[0]]);
	const res = await page.request.post('http://127.0.0.1:8080/api/reload.php', { data: { laps: 3 } });
	expect(res.ok()).toBeTruthy();

	const rows: any[] = await sql(
		`SELECT m.player, m.link, m.pts_before, m.pts_inc, j.pts_vs FROM mkmatches m
		 JOIN mkjoueurs j ON j.id = m.player
		 WHERE m.player IN (?) ORDER BY m.\`rank\``, [players]);
	expect(rows).toHaveLength(2);
	expect(rows.map(r => r.link)).toEqual([0, 0]);
	expect(rows.map(r => r.pts_before)).toEqual(before);
	// the winner gained, the other lost, and the account carries exactly the sum of the two
	expect(rows[0].pts_inc).toBeGreaterThan(0);
	expect(rows[1].pts_inc).toBeLessThan(0);
	for (const row of rows)
		expect(row.pts_vs).toBe(row.pts_before + row.pts_inc);

	await sql(`DELETE FROM mkplayers WHERE course = ?`, [room.insertId]);
	await sql(`DELETE FROM mariokart WHERE id = ?`, [room.insertId]);
	await sql(`UPDATE mkjoueurs SET course = 0 WHERE id = ?`, [players[0]]);
});
