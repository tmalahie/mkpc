import { randomBytes } from 'crypto';
import { sql } from './db';

// Topics are named with a random suffix, and cleanup matches the *shape* of that
// name rather than one run's value, so it clears this run's topic and any left
// behind by an earlier one. Anchored, so a real topic that merely starts with
// "New topic" is never matched.
const TOPIC_PATTERN = '^New topic [0-9a-f]{20}$';

export function newTopicName(): string {
  return 'New topic ' + randomBytes(10).toString('hex');
}

// Mirrors php/pages/supprtopic.php, because posting a topic touches more than
// mktopics/mkmessages: newtopic.php also follows the topic for its author and
// bumps mkprofiles.nbmessages. supprtopic.php is a CSRF-guarded HTML page rather
// than an API endpoint, so the cascade is reproduced instead of called.
export async function cleanupTopics() {
  const topics: any = await sql('SELECT id FROM mktopics WHERE titre REGEXP ?', [TOPIC_PATTERN]);
  if (!topics.length) return;
  const ids = topics.map((t: any) => t.id);

  // Before the messages go, so the per-author counts are still there to subtract.
  // nbmessages is `int unsigned` and MariaDB evaluates the subtraction before
  // GREATEST, so without the CAST an underflow errors instead of flooring at 0.
  await sql(
    `UPDATE mkprofiles p
     JOIN (SELECT auteur, COUNT(*) AS nb FROM mkmessages WHERE topic IN (?) GROUP BY auteur) c
       ON c.auteur = p.id
     SET p.nbmessages = GREATEST(CAST(p.nbmessages AS SIGNED) - c.nb, 0)`,
    [ids]
  );
  await sql('DELETE FROM mkfollowers WHERE topic IN (?)', [ids]);
  await sql('DELETE FROM mkmessages WHERE topic IN (?)', [ids]);
  await sql('DELETE FROM mktopics WHERE id IN (?)', [ids]);
  for (const table of ['mkreactions', 'mkreports', 'mkreportshist'])
    await sql(
      'DELETE FROM `' + table + '` WHERE type = "topic" AND SUBSTRING_INDEX(link, ",", 1) IN (?)',
      [ids]
    );
  // Goes further than supprtopic.php, which leaves these behind: newtopic.php
  // notifies every follower of the poster, and the seeded account has 386 of them.
  await sql(
    `DELETE FROM mknotifs
     WHERE type IN ('follower_topic', 'forum_mention', 'forum_quote')
       AND SUBSTRING_INDEX(link, ',', 1) IN (?)`,
    [ids]
  );
}
