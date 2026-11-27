import { FullConfig } from '@playwright/test';
import globalCleanup from './global-cleanup';
import { restoreLoungeSettings, removeLoungeBasics } from './helpers/lounge';

export default async function globalTeardown(config: FullConfig) {
  await globalCleanup(config);
  await restoreLoungeSettings();
  await removeLoungeBasics();
}
