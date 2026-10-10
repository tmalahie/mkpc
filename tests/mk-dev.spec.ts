import { test, expect, Page } from '@playwright/test';
import { waitForCupScreen, openCup } from './helpers/mkpc';

// Covers the dev-only tools of scripts/mk-dev.js: mkDevEval reaches the state
// scoped inside MarioKart(), mkDevEvents records the race flow so a test can
// wait on exact transitions instead of scraping the DOM, and the mkDev helpers
// start and drive races without going through the menus.

type DevEvent = { t: number; type: string; [key: string]: any };

function events(page: Page): Promise<DevEvent[]> {
  return page.evaluate(() => (window as any).mkDevEvents);
}

function screenFlow(list: DevEvent[]) {
  return list.map((e) => e.type === 'screen' ? e.name : e.type);
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

function karts(page: Page): Promise<any[]> {
  return page.evaluate(() => (window as any).mkDev.karts());
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

async function startRace(page: Page, options: Record<string, any>) {
  await page.goto('/mariokart.php');
  await waitForEvent(page, 'screen', { name: 'main' });
  await page.evaluate((options) => (window as any).mkDev.startRace(options), options);
  await waitForEvent(page, 'raceStart', { race: 1 });
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
  const startKarts = await karts(page);
  expect(startKarts[0]).toMatchObject({ id: 0, character: 'mario', cpu: false, lap: 1 });
  expect(startKarts.filter((k: any) => k.cpu)).toHaveLength(7);
  expect(await page.evaluate(() => (window as any).mkDev.spectatorId())).toBeUndefined();

  expect(screenFlow(await events(page))).toEqual([
    'main', 'characterSelect', 'cupSelect', 'trackSelect', 'loading', 'countdown', 'raceStart', 'race',
  ]);
  expect((await events(page)).find((e) => e.type === 'raceStart')).toMatchObject({ race: 1, map: 1, replay: false });

  await page.evaluate(() => (window as any).mkDev.finishRace());
  await waitForEvent(page, 'raceEnd', { race: 1, online: false });
  await waitForEvent(page, 'screen', { name: 'results' });
  expect((await karts(page))[0].lap).toBe(4);

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

test.describe('mkDev.startRace', () => {
  const MODES = [
    { options: { mode: 'VS', character: 'luigi', map: 2, players: 6 }, map: 2, karts: 6 },
    { options: { mode: 'GP', character: 'peach' }, map: 1, karts: 8 },
    { options: { mode: 'CM', character: 'toad', map: 5 }, map: 5, karts: 1 },
    { options: { mode: 'BB', character: 'yoshi' }, map: 57, karts: 4 },
  ];
  for (const { options, map, karts: nbKarts } of MODES) {
    test(`starts a ${options.mode} race without the menus`, async ({ page }) => {
      const errors: string[] = [];
      page.on('pageerror', (e) => errors.push(String(e)));
      await startRace(page, options);

      expect(screenFlow(await events(page))).toEqual(['main', 'loading', 'countdown', 'raceStart', 'race']);
      expect((await events(page)).find((e) => e.type === 'raceStart')).toMatchObject({ race: 1, map });
      const list = await karts(page);
      expect(list).toHaveLength(nbKarts);
      expect(list[0]).toMatchObject({ character: options.character, cpu: false });
      expect(errors).toEqual([]);
    });
  }

  test('refuses to start over a running race', async ({ page }) => {
    await startRace(page, {});
    await expect(page.evaluate(() => (window as any).mkDev.startRace())).rejects.toThrow(/menu screen/);
  });
});

test('race helpers drive a GP to its next race', async ({ page }) => {
  const errors: string[] = [];
  page.on('pageerror', (e) => errors.push(String(e)));
  await startRace(page, { mode: 'GP', character: 'toad', opponents: ['mario', 'luigi', 'peach', 'yoshi', 'bowser', 'daisy', 'wario'] });
  expect((await karts(page)).slice(1).map((k) => k.character).sort())
    .toEqual(['bowser', 'daisy', 'luigi', 'mario', 'peach', 'wario', 'yoshi']);

  await page.evaluate(() => (window as any).mkDev.giveItem('champi'));
  expect(await page.evaluate(() => (window as any).mkDev.kart(0).arme)).toBe('champi');
  await expect(page.evaluate(() => (window as any).mkDev.giveItem('not-an-item'))).rejects.toThrow(/give:/);

  await page.evaluate(() => (window as any).mkDev.setLap(2));
  expect((await karts(page))[0].lap).toBe(2);

  await page.evaluate(() => (window as any).mkDev.finishRace());
  await waitForEvent(page, 'raceEnd', { race: 1 });
  expect((await karts(page))[0].place).toBe(1);

  await clickButton(page, 'CONTINUE');
  await clickButton(page, 'CONTINUE');
  await clickButton(page, 'NEXT RACE');
  await waitForEvent(page, 'raceStart', { race: 2, map: 2 });
  expect(errors).toEqual([]);
});
