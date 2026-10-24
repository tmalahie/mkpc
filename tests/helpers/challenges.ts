import { APIRequestContext, Page, expect } from '@playwright/test';
import { sql } from './db';

// A member without the clvalidator right: publishing as Wargor (an admin) skips
// moderation, so the lifecycle needs a second account. Its profile pins it to an
// identifiant of its own, which makes it the owner of the tracks it creates, and
// the mkidentifiants row lifts the track-creation cooldown for it.
export const CHALLENGER = {
  name: 'e2e-challenger',
  password: 'e2e-password',
  identifiant: 4000000001,
};
const CHALLENGER_PASSWORD_HASH = '$2y$12$Ik5XDTjWPh/EBGo2KDPzDuMw9stPtSHorM0lINUwdOKLG1DYIOtvO';

const CHALLENGE_REWARDS = [1, 2, 5, 10, 20];

export async function ensureChallenger(): Promise<number> {
  await sql(
    `INSERT IGNORE INTO mkjoueurs (course, nom, code, joueur, choice_map, choice_rand, pts_vs, pts_battle, pts_challenge, online, banned, deleted)
     VALUES (0, ?, ?, '', 0, 0, 5000, 5000, 0, 0, 0, 0)`,
    [CHALLENGER.name, CHALLENGER_PASSWORD_HASH]
  );
  const [{ id }]: any = await sql('SELECT id FROM mkjoueurs WHERE nom = ?', [CHALLENGER.name]);
  await sql(
    `INSERT IGNORE INTO mkprofiles (id, identifiant, identifiant2, identifiant3, identifiant4, avatar, nick_color, nbmessages, email, country, description)
     VALUES (?, ?, 0, 0, 0, '', ?, 0, '', 0, '')`,
    [id, CHALLENGER.identifiant, CHALLENGER.name]
  );
  await sql('INSERT IGNORE INTO mkidentifiants SET identifiant = ?, disable_cooldown = 1', [CHALLENGER.identifiant]);
  return id;
}

// The seed makes Wargor an admin, which does not include the clvalidator right.
export async function ensureValidator(playerId: number) {
  await sql('INSERT IGNORE INTO mkrights SET player = ?, privilege = "clvalidator"', [playerId]);
}

export async function playerId(name: string): Promise<number> {
  const [{ id }]: any = await sql('SELECT id FROM mkjoueurs WHERE nom = ?', [name]);
  return id;
}

// Logs in through the same endpoint as the forum form. request shares the page's
// cookie jar, so the page is logged in afterwards.
export async function loginAs(request: APIRequestContext, name: string, password: string) {
  const res = await request.post('/api/testcode.php', { form: { pseudo: name, code: password } });
  expect(Number((await res.text()).trim()), 'login as ' + name).toBeGreaterThan(0);
}

export async function challengesOf(circuitId: number): Promise<any[]> {
  return sql(
    `SELECT c.id, c.clist, c.name, c.status, r.identifiant
     FROM mkchallenges c INNER JOIN mkclrace r ON r.id = c.clist
     WHERE r.type = "mkcircuits" AND r.circuit = ?`,
    [circuitId]
  );
}

export async function publisherOf(challengeId: number): Promise<number | undefined> {
  const rows: any = await sql('SELECT last_editor FROM mkpublishers WHERE type = "mkchallenges" AND creation_id = ?', [challengeId]);
  return rows[0]?.last_editor;
}

export async function creatorWins(challengeId: number): Promise<number[]> {
  const rows: any = await sql('SELECT player FROM mkclwin WHERE challenge = ? AND creator = 1 ORDER BY player', [challengeId]);
  return rows.map((r: any) => r.player);
}

function waitForDevEvent(page: Page, type: string) {
  return page.waitForFunction((type) => (window as any).mkDevEvents.some((e: any) => e.type === type), type, { timeout: 30_000 });
}

// Plays a VS race on the track page and finishes it 1st with the mk-dev helpers.
// The track's pending challenges are verified in the background, so a "complete
// the track" challenge succeeds without being selected.
export async function finishRaceOn(page: Page, url: string) {
  await page.goto(url);
  await waitForDevEvent(page, 'screen');
  await page.evaluate(() => (window as any).mkDev.startRace({ mode: 'VS' }));
  await waitForDevEvent(page, 'raceStart');
  const succeeded = page.waitForResponse((res) => res.url().includes('/api/challengeSucceeded.php'));
  await page.evaluate(() => (window as any).mkDev.finishRace());
  expect((await succeeded).ok()).toBe(true);
}

// Removes the challenges of e2e tracks and of the e2e account, with everything they
// leave behind: wins (and the points activation gave for them), attempts,
// moderation records and logs, publishers and notifications. Runs before the
// creation sweep, which deletes the tracks and would orphan active challenges.
export async function cleanupChallenges() {
  const lists: any = await sql(
    `SELECT r.id FROM mkclrace r
     LEFT JOIN mkcircuits c ON r.type = "mkcircuits" AND r.circuit = c.id
     WHERE c.auteur LIKE "e2e-%" OR r.identifiant = ?`,
    [CHALLENGER.identifiant]
  );
  const listIds = lists.map((r: any) => r.id);
  const challengerRows: any = await sql('SELECT id FROM mkjoueurs WHERE nom = ?', [CHALLENGER.name]);
  const challengerId = challengerRows[0]?.id;

  if (listIds.length) {
    const challenges: any = await sql('SELECT id FROM mkchallenges WHERE clist IN (?)', [listIds]);
    const ids = challenges.map((c: any) => c.id);
    if (ids.length) {
      await sql(
        `UPDATE mkjoueurs j
         JOIN (SELECT w.player, SUM(ELT(c.difficulty + 1, ?)) AS pts
               FROM mkclwin w INNER JOIN mkchallenges c ON c.id = w.challenge
               WHERE c.id IN (?) AND c.status = "active" GROUP BY w.player) x ON x.player = j.id
         SET j.pts_challenge = GREATEST(j.pts_challenge - x.pts, 0)`,
        [CHALLENGE_REWARDS, ids]
      );
      for (const table of ['mkclwin', 'mkcltry', 'mkclvalidations', 'mkclorder'])
        await sql('DELETE FROM `' + table + '` WHERE challenge IN (?)', [ids]);
      await sql('DELETE FROM mkpublishers WHERE type = "mkchallenges" AND creation_id IN (?)', [ids]);
      await sql('DELETE FROM mknotifs WHERE type IN ("challenge_moderated", "follower_challenge") AND link IN (?)', [ids.map(String)]);
      const logs: any = await sql(
        'SELECT id FROM mklogs WHERE SUBSTRING_INDEX(log, " ", 1) LIKE "%Challenge" AND SUBSTRING_INDEX(log, " ", -1) IN (?)',
        [ids.map(String)]
      );
      if (logs.length) {
        const logIds = logs.map((l: any) => l.id);
        await sql('DELETE FROM mklogsnapshots WHERE log IN (?)', [logIds]);
        await sql('DELETE FROM mklogs WHERE id IN (?)', [logIds]);
      }
      await sql('DELETE FROM mkchallenges WHERE id IN (?)', [ids]);
    }
    await sql('DELETE FROM mkclrace WHERE id IN (?)', [listIds]);
  }
  // challengeDel.php hard-deletes a challenge without real winners but keeps its
  // wins, so the account's own wins and points are reset rather than looked up.
  if (challengerId) {
    await sql('DELETE FROM mkclwin WHERE player = ?', [challengerId]);
    await sql('DELETE FROM mknotifs WHERE user = ?', [challengerId]);
    await sql('UPDATE mkjoueurs SET pts_challenge = 0 WHERE id = ?', [challengerId]);
  }
}
