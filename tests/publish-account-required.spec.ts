import { test, expect } from '@playwright/test';
import { sql } from './helpers/db';
import { login, SIMPLE_CIRCUIT_PIECES } from './helpers/mkpc';

const AUTHOR = 'e2e-logged-out';

function simpleCircuitForm(): Record<string, string> {
  const form: Record<string, string> = { nom: AUTHOR, auteur: AUTHOR, map: '1', nl: '3' };
  SIMPLE_CIRCUIT_PIECES.forEach((p, i) => (form['p' + i] = p));
  return form;
}

function simpleCircuitQuery(): string {
  const { nom, auteur, ...params } = simpleCircuitForm();
  return new URLSearchParams(params).toString();
}

test('a logged-out visitor cannot publish a circuit', async ({ page }) => {
  const res = await page.request.post('/api/saveCreation.php', { form: simpleCircuitForm() });
  expect((await res.text()).trim()).toBe('');
  const rows: any = await sql('SELECT COUNT(*) AS n FROM mkcircuits WHERE auteur = ?', [AUTHOR]);
  expect(Number(rows[0].n)).toBe(0);
});

test('the share button asks a logged-out visitor to log in', async ({ page }) => {
  await page.goto('/circuit.php?' + simpleCircuitQuery());
  await page.locator('#shareRace').click();
  const modal = page.locator('#accountRequired');
  await expect(modal).toBeVisible();
  await expect(modal).toContainText('You need to be logged in to share your creations');
  await expect(page.locator('#cSave')).toBeHidden();

  const loginTab = page.waitForEvent('popup');
  await modal.getByRole('button', { name: 'Log in / Register' }).click();
  await expect(await loginTab).toHaveURL(/forum\.php$/);
  await expect(modal).toBeHidden();

  await page.locator('#shareRace').click();
  await page.keyboard.press('Escape');
  await expect(modal).toBeHidden();
});

test('the share button opens the share form once logged in', async ({ page }) => {
  await login(page);
  await page.goto('/circuit.php?' + simpleCircuitQuery());
  await page.locator('#shareRace').click();
  await expect(page.locator('#cSave')).toBeVisible();
});
