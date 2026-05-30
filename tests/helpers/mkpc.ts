import { Page, APIRequestContext, expect } from '@playwright/test';

// Shared helpers for end-to-end tests that build their own creations
// (circuits -> cups -> multicup) and then play them.
//
// Creations are owned by an "identifiant" derived from the browser context
// (see php/includes/getId.php). Because page.request shares the page's cookie
// jar, every saveX.php call below runs under the same identity, so the
// ownership checks in saveCup.php / saveMCup.php pass without extra plumbing.

const ADMIN_USER = 'wargor';
const ADMIN_PASSWORD = 'aaaa';

// A known-valid 6x6 piece layout for a simple-mode circuit on base map 1
// (copied from a real shared circuit). saveCreation.php only requires p0..p35
// to be numeric, but a real loop keeps the track playable.
export const SIMPLE_CIRCUIT_PIECES =
  '11,5,4,5,9,4,11,8,8,8,5,7,11,8,8,8,6,4,11,8,6,7,11,2,5,7,11,11,11,8,6,9,9,9,9,7'.split(',');

const TEST_AUTHOR = 'e2e-mc-bot';

export async function login(page: Page) {
  await page.goto('/');
  await page.getByRole('menuitem', { name: 'Forum' }).click();
  await page.getByLabel('Login:').fill(ADMIN_USER);
  await page.getByLabel('Password:').fill(ADMIN_PASSWORD);
  await page.getByRole('button', { name: 'Submit' }).click();
  // Land on a normal page so session.php establishes the identity cookie.
  await page.goto('/');
}

async function postInt(request: APIRequestContext, url: string, form: Record<string, string>): Promise<number> {
  const res = await request.post(url, { form });
  const body = (await res.text()).trim();
  return Number(body);
}

// Creates a shared simple-mode circuit (mkcircuits, type=0). Returns its id.
// Throws on the per-identifiant anti-spam cooldown (max 2 new tracks / 60s,
// 5 / 5min - see isTrackCooldowned in php/includes/utils-cooldown.php), which
// is the only thing that makes track creation non-repeatable; cups/multicups
// have no such limit.
export async function createCircuit(
  request: APIRequestContext,
  opts: { name?: string; map?: string; laps?: string } = {}
): Promise<number> {
  const form: Record<string, string> = {
    nom: opts.name ?? 'e2e-circuit',
    auteur: TEST_AUTHOR,
    map: opts.map ?? '1',
    nl: opts.laps ?? '3',
  };
  SIMPLE_CIRCUIT_PIECES.forEach((p, i) => (form['p' + i] = p));
  const id = await postInt(request, '/api/saveCreation.php', form);
  if (!(id > 0))
    throw new Error(
      'createCircuit failed (got ' + id + '). Likely the track-creation cooldown ' +
      '(max 2 new tracks / 60s per user). Wait ~60s and retry.'
    );
  return id;
}

// Creates circuits up to `count`, degrading gracefully if the cooldown kicks
// in: returns at least one id, padding the array by reusing earlier ids so a
// caller can always index [0..count). A multicup only needs cups, and a cup
// accepts the same circuit in several slots, so one circuit is enough to build
// an arbitrarily large multicup.
export async function createCircuits(request: APIRequestContext, count: number): Promise<number[]> {
  const ids: number[] = [];
  for (let i = 0; i < count; i++) {
    try {
      ids.push(await createCircuit(request, { name: 'e2e-circuit-' + (i + 1) }));
    } catch (e) {
      if (ids.length === 0) throw e; // no circuit at all -> cannot build anything
      break; // cooldown after the first: reuse what we have
    }
  }
  const available = ids.length;
  while (ids.length < count) ids.push(ids[ids.length % available]);
  return ids;
}

// Creates a simple-mode cup (mode 0) referencing 4 circuit slots. Returns its id.
export async function createCup(
  request: APIRequestContext,
  opts: { name: string; circuitIds: number[]; options?: object }
): Promise<number> {
  const form: Record<string, string> = { nom: opts.name, auteur: TEST_AUTHOR, mode: '0' };
  for (let i = 0; i < 4; i++) form['cid' + i] = String(opts.circuitIds[i % opts.circuitIds.length]);
  if (opts.options) form.opt = JSON.stringify(opts.options);
  const id = await postInt(request, '/api/saveCup.php', form);
  expect(id, 'createCup should return a positive id').toBeGreaterThan(0);
  return id;
}

// Creates a simple-mode multicup (mode 0) from a list of cup ids. `options`
// holds the multicup appearance config (icons / lines / pages / persos).
export async function createMulticup(
  request: APIRequestContext,
  opts: { name: string; cupIds: number[]; options?: object }
): Promise<number> {
  const form: Record<string, string> = { nom: opts.name, auteur: TEST_AUTHOR, mode: '0' };
  opts.cupIds.forEach((id, i) => (form['cid' + i] = String(id)));
  if (opts.options) form.opt = JSON.stringify(opts.options);
  const id = await postInt(request, '/api/saveMCup.php', form);
  expect(id, 'createMulticup should return a positive id').toBeGreaterThan(0);
  return id;
}

// A 1x1 transparent gif used as a "custom" cup icon. selectMapScreen treats a
// string icon as a custom image URL (vs a number = built-in cup), so this lets
// us assert custom icons survived without hitting the network.
export const CUSTOM_ICON = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

// Drives the in-game menu from a freshly loaded play page to the multicup
// "Choose cup" screen: VS -> 1 player -> pick Mario.
export async function openMulticupCupScreen(page: Page) {
  await page.locator('input[type=button][value="VS"]').click();
  await page.locator('input[type=button]', { hasText: 'player' }).first().click();
  await page.locator('#perso-selector-mario').click();
  await waitForCupScreen(page);
}

function visibleCupImgsHandle(page: Page) {
  return page.evaluate(() => {
    const visible = (e: Element) => (e as HTMLElement).getClientRects().length > 0;
    const imgs = (Array.from(document.querySelectorAll('img.pixelated')) as HTMLImageElement[])
      .filter(visible)
      .filter((e) => e.style.cursor === 'pointer' && e.alt !== '');
    const rows = new Set(imgs.map((c) => c.style.top));
    return {
      nCups: imgs.length,
      nRows: rows.size,
      nCustom: imgs.filter((c) => c.src.startsWith('data:')).length,
      alts: imgs.map((c) => c.alt),
    };
  });
}

export async function waitForCupScreen(page: Page) {
  await page.waitForFunction(() => {
    const visible = (e: Element) => (e as HTMLElement).getClientRects().length > 0;
    return (Array.from(document.querySelectorAll('img.pixelated')) as HTMLImageElement[])
      .filter(visible)
      .some((e) => e.style.cursor === 'pointer' && e.alt !== '');
  });
}

// Reads the cup-selection screen layout: how many cup icons are shown, in how
// many rows, and how many use a custom (data-uri) image.
export type CupScreen = { nCups: number; nRows: number; nCustom: number; alts: string[] };
export async function readCupScreen(page: Page): Promise<CupScreen> {
  return (await visibleCupImgsHandle(page)) as CupScreen;
}

// Waits for the "Choose track" screen, recognised by its track previews (the
// only pixelated images with a silver border). Works for both single cups and
// multicup sub-cups (the latter has no Random button).
export async function waitForTrackScreen(page: Page) {
  await page.waitForFunction(() =>
    (Array.from(document.querySelectorAll('img.pixelated')) as HTMLImageElement[]).some(
      (e) => e.style.border.includes('silver') && e.getClientRects().length > 0
    )
  );
}

// Clicks the cup with the given alt (0-based index) and waits for the
// "Choose track" screen.
export async function openCup(page: Page, alt: number) {
  await page.evaluate((a) => {
    const visible = (e: Element) => (e as HTMLElement).getClientRects().length > 0;
    const img = (Array.from(document.querySelectorAll('img.pixelated')) as HTMLImageElement[])
      .filter(visible)
      .find((e) => e.style.cursor === 'pointer' && e.alt === String(a));
    img!.click();
  }, alt);
  await waitForTrackScreen(page);
}

// On the "Choose track" screen, presses Back to return to the cup screen.
export async function backToCupScreen(page: Page) {
  await page.locator('input[type=button][value="Back"]').first().click();
  await waitForCupScreen(page);
}
