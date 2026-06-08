import { test, expect } from '@playwright/test';
import {
  login,
  createCircuits,
  createCup,
  createMulticup,
  openMulticupCupScreen,
  readCupScreen,
  openCup,
  backToCupScreen,
  waitForTrackScreen,
  CUSTOM_ICON,
} from './helpers/mkpc';

// End-to-end coverage of the multicup flow: build a real multicup from scratch
// (circuits -> cups -> multicup), then play it and exercise the cup-selection
// screen. Includes a regression for the bug where pressing "Back" from the
// "Choose track" screen wiped the multicup's appearance (custom icons + pages).
//
// Layout under test: 6 cups, displayed as lines=[2,2,2] split into 2 pages by
// pages=[2]:
//   page 0 -> lines 0..1 -> 4 cups over 2 rows
//   page 1 -> line  2    -> 2 cups over 1 row
// Every cup uses a custom (data-uri) icon, so a reset to default icons is
// detectable.
const NB_CUPS = 6;
const MULTICUP_OPTIONS = {
  lines: [2, 2, 2],
  pages: [2],
  icons: Array(NB_CUPS).fill(CUSTOM_ICON),
};

// Serial: the multicup fixture is built once in beforeAll and shared (read-only)
// by every test. Without this, fullyParallel would run beforeAll once per worker,
// and the extra circuit creations would trip the anti-spam cooldown.
test.describe.configure({ mode: 'serial' });

test.describe('multicup', () => {
  let mid: number;
  let firstCupId: number;

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await login(page);
    const circuitIds = await createCircuits(page.request, 2);
    const cupIds: number[] = [];
    for (let i = 0; i < NB_CUPS; i++)
      cupIds.push(await createCup(page.request, { name: 'e2e-cup-' + (i + 1), circuitIds }));
    firstCupId = cupIds[0];
    mid = await createMulticup(page.request, {
      name: 'e2e-multicup',
      cupIds,
      options: MULTICUP_OPTIONS,
    });
    await page.close();
  });

  test('plays a freshly created multicup', async ({ page }) => {
    const errors: string[] = [];
    page.on('pageerror', (e) => errors.push(String(e)));
    await page.goto('/circuit.php?mid=' + mid);
    const info = await page.evaluate(() => ({
      cupPayloads: (window as any).cupPayloads?.length ?? null,
      iconCount: (window as any).cupOpts?.icons?.length ?? null,
    }));
    expect(info.cupPayloads).toBe(NB_CUPS);
    expect(info.iconCount).toBe(NB_CUPS);
    await page.locator('input[type=button][value="VS"]').waitFor();
    expect(errors).toEqual([]);
  });

  test('cup-selection screen honours custom icons and pagination', async ({ page }) => {
    await page.goto('/circuit.php?mid=' + mid);
    await openMulticupCupScreen(page);

    const page0 = await readCupScreen(page);
    expect(page0.nCups).toBe(4);
    expect(page0.nRows).toBe(2);
    expect(page0.nCustom).toBe(4); // all icons are custom data-uris

    // Next page (►): line 2 -> 2 cups on a single row.
    await page.evaluate(() => {
      const next = Array.from(document.querySelectorAll('input[type=button]'))
        .find((e) => (e as HTMLInputElement).value === '►') as HTMLInputElement;
      next.click();
    });
    await expect.poll(async () => (await readCupScreen(page)).nCups).toBe(2);
    const page1 = await readCupScreen(page);
    expect(page1.nRows).toBe(1);
    expect(page1.nCustom).toBe(2);
  });

  // Regression for the b87fa2e5 bug: applyMulticupCupOpts used to overwrite the
  // whole cupOpts (wiping lines/pages/icons) when a cup was picked, so returning
  // to the cup screen showed every cup on one page with default icons.
  test('Back from Choose track preserves page layout and custom icons', async ({ page }) => {
    await page.goto('/circuit.php?mid=' + mid);
    await openMulticupCupScreen(page);

    const before = await readCupScreen(page);
    expect(before.nCups).toBe(4);
    expect(before.nCustom).toBe(4);

    await openCup(page, Number(before.alts[0]));
    await backToCupScreen(page);

    const after = await readCupScreen(page);
    expect(after.nCups).toBe(before.nCups); // not all 6 cups
    expect(after.nRows).toBe(before.nRows);
    expect(after.nCustom).toBe(before.nCustom); // icons not reset to defaults
  });

  // A single cup has no cup-selection screen: picking VS -> 1 player -> a
  // character goes straight to "Choose track".
  test('single cup skips the cup-selection screen', async ({ page }) => {
    await page.goto('/circuit.php?cid=' + firstCupId);
    await page.locator('input[type=button][value="VS"]').click();
    await page.locator('input[type=button]', { hasText: 'player' }).first().click();
    await page.locator('#perso-selector-mario').click();
    await waitForTrackScreen(page);
    // Went straight to "Choose track": no cup-selection icons are shown, and a
    // single cup offers the Random button (multicups don't).
    expect((await readCupScreen(page)).nCups).toBe(0);
    await expect(page.locator('input[type=button][value="Random"]')).toBeVisible();
  });
});
