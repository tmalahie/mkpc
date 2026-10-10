import { test, expect, Page } from '@playwright/test';
import { waitForCupScreen, openCup } from './helpers/mkpc';

// Covers the dev-only hooks of scripts/mk-dev.js: mkDevEval reaches the state
// scoped inside MarioKart(), and mkDevEvents records the race flow so a test can
// wait on exact transitions instead of scraping the DOM.

type DevEvent = { t: number; type: string; [key: string]: any };

function events(page: Page): Promise<DevEvent[]> {
  return page.evaluate(() => (window as any).mkDevEvents);
}

function waitForEvent(page: Page, type: string, fields: Record<string, any> = {}) {
  return page.waitForFunction(
    ([type, fields]) => (window as any).mkDevEvents.some((e: any) =>
      e.type === type && Object.keys(fields).every((k) => e[k] === fields[k])
    ),
    [type, fields] as const,
    { timeout: 30_000 }
  );
}

async function startFirstTrack(page: Page) {
  await page.locator('input[type=button][value="VS"]').click();
  await page.locator('input[type=button]', { hasText: 'player' }).first().click();
  await page.locator('#perso-selector-mario').click();
  await waitForCupScreen(page);
  await openCup(page, 0);
  await page.evaluate(() => {
    const track = (Array.from(document.querySelectorAll('img.pixelated')) as HTMLImageElement[])
      .find((e) => e.style.border.includes('silver') && e.getClientRects().length > 0);
    track!.click();
  });
}

// Same as the commented-out /autoplay chat command in mk.js: the AI drives the
// player kart and the game runs one frame per tick, so a race takes seconds.
async function autoplay(page: Page) {
  await page.evaluate(() => (window as any).mkDevEval(
    'oPlayers[0].cpu = true; oPlayers[0].autoplay = true;' +
    'oPlayers[0].maxspeed = oPlayers[0].maxspeed0 = 5.7;' +
    'SPF = 1; clearInterval(cycleHandler); cycleHandler = null; cycle();'
  ));
}

async function clickButton(page: Page, value: string) {
  await page.locator(`input[type=button][value*="${value}"]:visible`).first().click();
}

test('exposes the game state and logs the race flow', async ({ page }) => {
  const errors: string[] = [];
  page.on('pageerror', (e) => errors.push(String(e)));

  await page.goto('/mariokart.php');
  await waitForEvent(page, 'screen', { name: 'main' });
  expect(await page.evaluate(() => (window as any).mkDevEval('aKarts').length)).toBe(0);

  await startFirstTrack(page);
  await waitForEvent(page, 'raceStart', { race: 1 });

  expect(await page.evaluate(() => (window as any).mkDevEval('aKarts').length)).toBe(8);
  const karts = await page.evaluate(() => (window as any).mkDev.karts());
  expect(karts[0]).toMatchObject({ id: 0, character: 'mario', cpu: false, lap: 1 });
  expect(karts.filter((k: any) => k.cpu)).toHaveLength(7);
  expect(await page.evaluate(() => (window as any).mkDev.spectatorId())).toBeUndefined();

  expect((await events(page)).map((e) => e.type === 'screen' ? e.name : e.type)).toEqual([
    'main', 'characterSelect', 'cupSelect', 'trackSelect', 'loading', 'countdown', 'raceStart', 'race',
  ]);
  expect((await events(page)).find((e) => e.type === 'raceStart')).toMatchObject({ race: 1, map: 1, replay: false });

  await autoplay(page);
  await waitForEvent(page, 'raceEnd', { race: 1, online: false });
  await waitForEvent(page, 'screen', { name: 'results' });
  expect((await page.evaluate(() => (window as any).mkDev.karts()))[0].lap).toBe(4);

  await clickButton(page, 'CONTINUE');
  await clickButton(page, 'CONTINUE');
  await clickButton(page, 'NEXT RACE');
  await waitForCupScreen(page);
  await openCup(page, 0);
  await page.evaluate(() => {
    const track = (Array.from(document.querySelectorAll('img.pixelated')) as HTMLImageElement[])
      .filter((e) => e.style.border.includes('silver') && e.getClientRects().length > 0)[1];
    track.click();
  });
  await waitForEvent(page, 'raceStart', { race: 2 });
  expect(await page.evaluate(() => (window as any).mkDev.raceCount())).toBe(2);

  expect(errors).toEqual([]);
});

test('mkDevEval follows the current game after quitting', async ({ page }) => {
  await page.goto('/mariokart.php');
  await startFirstTrack(page);
  await waitForEvent(page, 'raceStart', { race: 1 });

  const mainScreens = async () => (await events(page)).filter((e) => e.type === 'screen' && e.name === 'main').length;
  expect(await mainScreens()).toBe(1);
  await page.evaluate(() => (window as any).mkDevEval('quitter()'));
  await page.waitForFunction(() =>
    (window as any).mkDevEvents.filter((e: any) => e.type === 'screen' && e.name === 'main').length === 2
  );
  expect(await page.evaluate(() => (window as any).mkDevEval('aKarts').length)).toBe(0);
});
