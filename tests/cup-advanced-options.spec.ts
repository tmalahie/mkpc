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

  // Sub-sections are collapsed by default: no CPU rows, no point rows
  await expect(page.locator('.gp-cpu-row')).toHaveCount(0);
  await expect(page.locator('.gp-point-row')).toHaveCount(0);

  // Expand CPU drivers via "Custom..."
  await page.locator('.gp-cpus-mode').selectOption({ value: 'custom' });
  await expect(page.locator('.gp-cpu-row')).toHaveCount(7);
  // Add CPU button extends the list
  await page.locator('.gp-add-cpu').click();
  await expect(page.locator('.gp-cpu-row')).toHaveCount(8);
  // Remove CPU shrinks
  await page.locator('.gp-remove-cpu').click();
  await expect(page.locator('.gp-cpu-row')).toHaveCount(7);
  await page.locator('.gp-remove-cpu').click();
  await expect(page.locator('.gp-cpu-row')).toHaveCount(6);

  // Expand Point distribution via "Custom...": tracks CPU count + 1 = 7 inputs
  await page.locator('.gp-points-mode').selectOption({ value: 'custom' });
  await expect(page.locator('.gp-point-row input[type=number]')).toHaveCount(7);

  // Change global difficulty to Easy, then override CPU 1 to Impossible
  await page.locator('.gp-options-base .gp-base-row select').first().selectOption({ label: 'Easy' });
  await page.locator('.gp-cpu-row select').nth(1).selectOption({ label: 'Impossible' });
  const optionsValue = await page.locator('#cup-options').inputValue();
  const parsed = JSON.parse(optionsValue);
  expect(parsed.gp.difficulty).toBe(0);
  expect(parsed.gp.cpus[0].difficulty).toBe(4);
  // CPUs 2..6 inherit (null)
  expect(parsed.gp.cpus[1].difficulty).toBeNull();
  expect(parsed.gp.cpus).toHaveLength(6);
  expect(parsed.gp.points).toHaveLength(7);
});

test('CPU drivers and Points default to collapsed and round-trip clean', async ({ page }) => {
  await login(page);
  await page.goto('/simplecup.php');
  await page.locator('.editor-switch-options').first().click();

  // Only base settings rows are present initially — no CPU sub-list, no points inputs
  await expect(page.locator('.gp-cpu-list')).toHaveCount(0);
  await expect(page.locator('.gp-points-list')).toHaveCount(0);

  // With everything on "Default", cup-options should not include gp at all
  const initial = await page.locator('#cup-options').inputValue();
  expect(initial).toBe('');

  // Expand CPU drivers then collapse: gpOpts.cpus should be cleared
  await page.locator('.gp-cpus-mode').selectOption({ value: 'custom' });
  await expect(page.locator('.gp-cpu-list')).toHaveCount(1);
  await page.locator('.gp-cpus-mode').selectOption({ value: 'default' });
  await expect(page.locator('.gp-cpu-list')).toHaveCount(0);
  const collapsed = await page.locator('#cup-options').inputValue();
  expect(collapsed).toBe('');
});

test('multicup editor no longer has a Grand Prix tab', async ({ page }) => {
  await login(page);
  await page.goto('/simplecups.php');

  await page.locator('.editor-switch-options').first().click();

  const tabs = page.locator('#option-tabs > div');
  await expect(tabs).toHaveCount(2);
  await expect(tabs.nth(0)).toContainText('Multicup appearance');
  await expect(tabs.nth(1)).toContainText('Character roster');
  await expect(page.locator('#gp-options')).toHaveCount(0);
});

test('cup editor saves item distribution selection', async ({ page }) => {
  await login(page);
  await page.goto('/simplecup.php');
  await page.locator('.editor-switch-options').first().click();

  // Pick "Bob-ombs". In itemDistributions.VS the order is
  // Standard(0), Aggressive mode(1), Shells(2), Bob-ombs(3), Mushrooms(4), so index 3.
  await page.locator('#gp-items').selectOption({ label: 'Bob-ombs' });
  const optionsValue = await page.locator('#cup-options').inputValue();
  const parsed = JSON.parse(optionsValue);
  expect(parsed.gp.items).toEqual({ index: 3 });
});

test('cup editor preserves orphaned custom item distribution', async ({ page }) => {
  await login(page);
  // Pre-seed cupOpts with a custom item distribution whose name doesn't exist
  // in this user's localStorage. Round-trip via the editor and confirm it isn't
  // dropped.
  await page.goto('/simplecup.php');
  await page.evaluate(() => {
    // @ts-ignore
    gpOpts = { items: { name: 'GhostSet', value: [{ champi: 1 }] } };
    // @ts-ignore
    showEditorContent(1);
    // @ts-ignore
    resetCupOptions();
  });
  const sel = await page.locator('#gp-items').evaluate((el: HTMLSelectElement) => ({
    value: el.value,
    selectedText: el.options[el.selectedIndex].textContent,
  }));
  expect(sel.value).toBe('o');
  expect(sel.selectedText).toBe('Custom');
  const optionsValue = await page.locator('#cup-options').inputValue();
  const parsed = JSON.parse(optionsValue);
  expect(parsed.gp.items).toEqual({ name: 'GhostSet', value: [{ champi: 1 }] });
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
  }));
  expect(data.cupOpts.gp.cpus[0].difficulty).toBe(4);
  expect(data.cupOpts.gp.cpus[1].difficulty).toBe(0);
  expect(data.cupOpts.gp.cc).toBe(200);
  expect(data.cupOpts.gp.points).toEqual([12, 6, 0]);
});
