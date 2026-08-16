import { sql } from './db';

// The lounge tests queue up as the seeded account, and a queue that reached
// "locked" or beyond cannot be released through leave.php - which is exactly the
// state that wedges an account for good, so it has to be cleared out of band.
//
// Scoped to the accounts the tests play as, not to what a given run created, so an
// interrupted run is repaired by the next one.
const TEST_ACCOUNTS = ['Wargor'];

// mklounge_players is deliberately left alone: those rows are a player's season
// record (rating, mogis played), and the same account is used for manual testing.
export async function cleanupLoungeQueues() {
  await sql(
    `UPDATE mklounge_queues q
     JOIN mklounge_queue_members m ON m.queue = q.id
     JOIN mkjoueurs j ON j.id = m.player
     SET q.status = 'cancelled'
     WHERE q.status NOT IN ('cancelled', 'finished') AND j.nom IN (?)`,
    [TEST_ACCOUNTS]
  );
  await sql(
    `UPDATE mklounge_queue_members m
     JOIN mkjoueurs j ON j.id = m.player
     SET m.dropped_at = NOW()
     WHERE m.dropped_at IS NULL AND j.nom IN (?)`,
    [TEST_ACCOUNTS]
  );
}
