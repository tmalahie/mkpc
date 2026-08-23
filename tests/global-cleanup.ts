import { FullConfig } from '@playwright/test';
import { cleanupCreations } from './helpers/mkpc';
import { cleanupTopics } from './helpers/forum';
import { cleanupLoungeQueues, cleanupLoungeFixtures } from './helpers/lounge';

// Runs once before the suite and once after it, outside every worker.
//
// Cleanup used to live in per-spec beforeAll/afterAll hooks, which was the wrong
// place for it twice over: those hooks run once per *worker*, so a worker
// finishing early would delete fixtures another worker was still using, and the
// only way to prevent that was to make each spec serial - paying for cleanup with
// the suite's parallelism. Here nothing else is running, so neither applies.
//
// Both ends are wired up on purpose. afterAll is the contract; the before pass is
// what protects this run, because a killed run never reaches the after pass, and
// that is exactly when leftovers get created. Each sweep clears everything in its
// scope rather than what this run happens to have made, so an interrupted run is
// repaired by the next one instead of poisoning it.
export default async function globalCleanup(config: FullConfig) {
  const baseURL = config.projects[0]?.use?.baseURL;
  if (!baseURL) throw new Error('cleanup needs a baseURL: set use.baseURL or BASE_URL');
  await cleanupCreations(baseURL);
  await cleanupTopics();
  await cleanupLoungeQueues();
  await cleanupLoungeFixtures();
}
