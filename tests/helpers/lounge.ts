import { sql } from './db';

// The lounge tests queue up as the seeded account, and a queue that reached
// "locked" or beyond cannot be released through leave.php - which is exactly the
// state that wedges an account for good, so it has to be cleared out of band.
const SEEDED_ACCOUNT = 'Wargor';

// Staged matches need a lineup, and the seeded database has one usable account, so
// the specs create throwaway players. One prefix for all of them, so cleanup has a
// single scope to sweep; each spec passes its own tag so two spec files running in
// parallel do not see each other's players.
export const LOUNGE_BOT_PREFIX = 'e2e-lounge-';

export function loungeBotPattern(tag: string): string {
  return LOUNGE_BOT_PREFIX + tag + '-%';
}

// Private-game keys are normally random, so the specs that stage a finished match
// take them from a reserved range. Cleanup sweeps the range rather than a list of
// keys the specs would have to keep in sync with it.
export const LOUNGE_KEY_MIN = 990000;
export const LOUNGE_KEY_MAX = 999999;

// Same bcrypt hash the seeded account uses (see docker/php/scripts/initdb.sh), so the
// throwaway players can log in through testcode.php and drive the real API flows
// instead of contending with the seeded account for its single queue slot.
export const LOUNGE_BOT_PASSWORD = 'aaaa';
const LOUNGE_BOT_HASH = '$2y$10$DHPgMFxb56xU.ohu3ildtuhfHcFUcqwz0HilUn6p9UMnSM/tqGwnO';

// Also used mid-test, to release a queue that has passed the point where leave.php
// will let a player out.
//
// mklounge_players is deliberately spared for the real accounts: those rows are a
// player's season record (rating, mogis played), and the same account is used for
// manual testing. The throwaway players have no such history, so they go entirely.
// namePattern is a LIKE pattern, so a bare name matches only itself.
export async function cleanupLoungeQueues(namePattern: string = SEEDED_ACCOUNT) {
  await sql(
    `UPDATE mklounge_queues q
     JOIN mklounge_queue_members m ON m.queue = q.id
     JOIN mkjoueurs j ON j.id = m.player
     SET q.status = 'cancelled'
     WHERE q.status NOT IN ('cancelled', 'finished') AND j.nom LIKE ?`,
    [namePattern]
  );
  await sql(
    `UPDATE mklounge_queue_members m
     JOIN mkjoueurs j ON j.id = m.player
     SET m.dropped_at = NOW()
     WHERE m.dropped_at IS NULL AND j.nom LIKE ?`,
    [namePattern]
  );
}

// Scoped by the bot prefix and the reserved key range rather than by what this run
// created, so an interrupted run is repaired by the next one.
export async function cleanupLoungeFixtures() {
  const bots: any = await sql('SELECT id FROM mkjoueurs WHERE nom LIKE ?', [LOUNGE_BOT_PREFIX + '%']);
  const ids = bots.map((r: any) => r.id);
  if (ids.length) {
    await sql('DELETE FROM mklounge_match_players WHERE player IN (?)', [ids]);
    await sql('DELETE FROM mklounge_queue_members WHERE player IN (?)', [ids]);
    await sql('DELETE FROM mklounge_players WHERE player IN (?)', [ids]);
    await sql('DELETE FROM mkgamerank WHERE player IN (?)', [ids]);
    await sql('DELETE FROM mkjoueurs WHERE id IN (?)', [ids]);
  }
  const range = [LOUNGE_KEY_MIN, LOUNGE_KEY_MAX];
  await sql(
    `DELETE mp FROM mklounge_match_players mp
     JOIN mklounge_matches m ON m.id = mp.\`match\`
     WHERE m.privgame_key BETWEEN ? AND ?`,
    range
  );
  await sql('DELETE FROM mklounge_matches WHERE privgame_key BETWEEN ? AND ?', range);
  await sql('DELETE FROM mklounge_queues WHERE privgame_key BETWEEN ? AND ?', range);
  await sql('DELETE FROM mkgamedata WHERE game BETWEEN ? AND ?', range);
  await sql('DELETE FROM mkgamerank WHERE game BETWEEN ? AND ?', range);
}

// Throwaway players for a staged lineup. mkjoueurs has no defaults for these
// columns, so they are all spelled out.
export async function createLoungeBots(count: number, tag: string): Promise<number[]> {
  const ids: number[] = [];
  for (let i = 1; i <= count; i++) {
    const res: any = await sql(
      `INSERT INTO mkjoueurs
        (nom, course, code, joueur, choice_map, choice_rand, pts_vs, pts_battle, pts_challenge, online, deleted)
       VALUES (?, 0, ?, 'mario', 0, 0, 0, 0, 0, 0, 0)`,
      [LOUNGE_BOT_PREFIX + tag + '-' + i, LOUNGE_BOT_HASH]
    );
    ids.push(res.insertId);
  }
  return ids;
}

export function loungeBotName(tag: string, index: number): string {
  return LOUNGE_BOT_PREFIX + tag + '-' + index;
}
