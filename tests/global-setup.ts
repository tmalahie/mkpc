import { FullConfig } from '@playwright/test';
import globalCleanup from './global-cleanup';
import { stashLoungeSettings, ensureLoungeBasics } from './helpers/lounge';

// The snapshot has to be taken before the sweep, because the sweep is what empties the table.
export default async function globalSetup(config: FullConfig) {
  await stashLoungeSettings();
  await ensureLoungeBasics();
  await globalCleanup(config);
}
