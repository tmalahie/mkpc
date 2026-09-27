import { test, expect } from '@playwright/test';
import { sql } from './helpers/db';
import { login, createCircuit, SIMPLE_CIRCUIT_PIECES } from './helpers/mkpc';

const AUTHOR = 'e2e-logged-out';
const ADMIN_USER = 'wargor';
const ADMIN_PASSWORD = 'aaaa';

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

  await page.keyboard.press('Escape');
  await expect(modal).toBeHidden();
});

test('logging in from the modal lets the visitor share without reloading', async ({ page }) => {
  await page.goto('/circuit.php?' + simpleCircuitQuery());
  await page.locator('#shareRace').click();
  const modal = page.locator('#accountRequired');

  const loginTabEvent = page.waitForEvent('popup');
  await modal.getByRole('button', { name: 'Log in / Register' }).click();
  const loginTab = await loginTabEvent;
  await expect(loginTab).toHaveURL(/forum\.php$/);
  await loginTab.getByLabel('Login:').fill(ADMIN_USER);
  await loginTab.getByLabel('Password:').fill(ADMIN_PASSWORD);
  await loginTab.getByRole('button', { name: 'Submit' }).click();
  await loginTab.waitForLoadState();

  await page.bringToFront();
  await page.evaluate(() => window.dispatchEvent(new Event('focus')));
  await expect(modal).toContainText('You are now logged in');
  await modal.getByRole('button', { name: 'Share now' }).click();
  await expect(modal).toBeHidden();
  await expect(page.locator('#cSave')).toBeVisible();
});

test('the share button opens the share form once logged in', async ({ page }) => {
  await login(page);
  await page.goto('/circuit.php?' + simpleCircuitQuery());
  await page.locator('#shareRace').click();
  await expect(page.locator('#cSave')).toBeVisible();
});

test('publishing records the account that published, and deleting forgets it', async ({ page }) => {
  await login(page);
  const [{ id: playerId }]: any = await sql('SELECT id FROM mkjoueurs WHERE nom = ?', ['wargor']);
  const circuitId = await createCircuit(page.request, { author: 'e2e-publisher' });
  const publishers = () =>
    sql('SELECT publisher, last_editor FROM mkpublishers WHERE type = "mkcircuits" AND creation_id = ?', [circuitId]);

  expect(await publishers()).toEqual([{ publisher: playerId, last_editor: playerId }]);

  await page.request.post('/api/supprCreation.php', { form: { id: String(circuitId), collab: '' } });
  expect(await publishers()).toEqual([]);
});

test('unsharing a track someone else owns leaves its publisher record alone', async ({ page }) => {
  const unownedId = 2147483000;
  await sql('INSERT INTO mkpublishers SET type = "circuits", creation_id = ?, publisher = 1, last_editor = 1', [unownedId]);
  try {
    await page.request.post('/api/supprDraw.php', { form: { id: String(unownedId), collab: '' } });
    const rows: any = await sql('SELECT COUNT(*) AS n FROM mkpublishers WHERE type = "circuits" AND creation_id = ?', [unownedId]);
    expect(Number(rows[0].n)).toBe(1);
  } finally {
    await sql('DELETE FROM mkpublishers WHERE type = "circuits" AND creation_id = ?', [unownedId]);
  }
});
