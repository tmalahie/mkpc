import { test, expect } from '@playwright/test';

const ADMIN_USER = 'wargor';
const ADMIN_PASSWORD = 'aaaa';

async function login(page: import('@playwright/test').Page) {
  await page.goto('/');
  await page.getByRole('menuitem', { name: 'Forum' }).click();
  await page.getByLabel('Login:').fill(ADMIN_USER);
  await page.getByLabel('Password:').fill(ADMIN_PASSWORD);
  await page.getByRole('button', { name: 'Submit' }).click();
}

test('simple cup editor shows Advanced Options with per-CPU rows', async ({ page }) => {
  await login(page);
  await page.goto('/simplecup.php');

  await expect(page.locator('.editor-switch-options').first()).toBeVisible();
  await page.locator('.editor-switch-options').first().click();
  await expect(page.locator('#gp-options')).toBeVisible();

  // 7 default CPU rows
  await expect(page.locator('.gp-cpu-row')).toHaveCount(7);
  // Add CPU button extends the list
  await page.locator('.gp-add-cpu').click();
  await expect(page.locator('.gp-cpu-row')).toHaveCount(8);
  // Remove CPU shrinks
  await page.locator('.gp-remove-cpu').click();
  await expect(page.locator('.gp-cpu-row')).toHaveCount(7);
  await page.locator('.gp-remove-cpu').click();
  await expect(page.locator('.gp-cpu-row')).toHaveCount(6);

  // Point distribution auto-tracks: 6 CPUs + 1 human = 7 inputs
  await expect(page.locator('.gp-point-row input[type=number]')).toHaveCount(7);

  // Change CPU 1's difficulty and the hidden input should reflect it
  await page.locator('.gp-cpu-row select').nth(1).selectOption({ label: 'Impossible' });
  const optionsValue = await page.locator('#cup-options').inputValue();
  const parsed = JSON.parse(optionsValue);
  expect(parsed.gp.cpus[0].difficulty).toBe(4);
  expect(parsed.gp.cpus).toHaveLength(6);
  expect(parsed.gp.points).toHaveLength(7);
});

test('multicup editor still shows three option tabs', async ({ page }) => {
  await login(page);
  await page.goto('/simplecups.php');

  await page.locator('.editor-switch-options').first().click();

  const tabs = page.locator('#option-tabs > div');
  await expect(tabs).toHaveCount(3);
  await expect(tabs.nth(0)).toContainText('Multicup appearance');
  await expect(tabs.nth(1)).toContainText('Character roster');
  await expect(tabs.nth(2)).toContainText('Grand Prix');
});

test('circuit runtime parses cupOpts.gp from URL', async ({ page }) => {
  await login(page);

  const optsJson = JSON.stringify({
    gp: {
      cpus: [
        { driver: '', difficulty: 4 },
        { driver: '', difficulty: 0 }
      ],
      cc: 200,
      points: [12, 6, 0]
    }
  });
  await page.goto('/circuit.php?cid0=1&cid1=2&cid2=3&cid3=1&opt=' + encodeURIComponent(optsJson));
  await page.waitForFunction(() => typeof (window as any).cupOpts !== 'undefined');

  const data = await page.evaluate(() => ({
    cupOpts: (window as any).cupOpts,
    selectedCc: (window as any).selectedCc
  }));
  expect(data.cupOpts.gp.cpus[0].difficulty).toBe(4);
  expect(data.cupOpts.gp.cpus[1].difficulty).toBe(0);
  expect(data.cupOpts.gp.cc).toBe(200);
  expect(data.cupOpts.gp.points).toEqual([12, 6, 0]);
  expect(data.selectedCc).toBe('200');
});
