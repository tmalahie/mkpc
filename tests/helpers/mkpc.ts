import { Page, APIRequestContext, expect, request as apiRequest } from '@playwright/test';
import { sql } from './db';

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

// Every author tag a spec creates under must match this, or its fixtures survive
// the sweep in tests/global-cleanup.ts.
const TEST_AUTHOR_PATTERN = 'e2e-%';

// saveCreation/saveCup/saveMCup each notify every follower of the poster, and no
// delete path removes those rows. The prefix in `link` identifies the kind of
// creation: 0=circuit, 3=cup, 4=multicup.
const NOTIF_PREFIX = { circuits: 0, cups: 3, mcups: 4 };

async function deleteFollowerNotifs(kind: keyof typeof NOTIF_PREFIX, ids: string[]) {
  if (!ids.length) return;
  await sql('DELETE FROM mknotifs WHERE type = "follower_circuit" AND link IN (?)',
    [ids.map((id) => NOTIF_PREFIX[kind] + ',' + id)]);
}

// Removes every creation whose author matches TEST_AUTHOR_PATTERN, whether this
// run or an earlier one made it, so a crashed run is healed instead of leaving
// fixtures behind forever.
//
// Matching a pattern rather than a list of owners is what keeps this out of the
// spec files: a new spec only has to tag its creations `e2e-*` to be cleaned up,
// with nothing to register here.
//
// Circuits go through supprCreation.php rather than raw SQL: the endpoint cascades
// to cups, orphaned multicups, records, ghosts and challenge references, and
// unlinks the track thumbnail from disk (postCircuitDelete).
export async function cleanupCreations(baseURL: string) {
  const scope = async (table: string): Promise<string[]> =>
    (await sql('SELECT id FROM `' + table + '` WHERE auteur LIKE ?', [TEST_AUTHOR_PATTERN]))
      .map((r: any) => String(r.id));
  const circuits = await scope('mkcircuits');
  const cups = await scope('mkcups');
  const mcups = await scope('mkmcups');

  if (circuits.length) {
    const ctx = await apiRequest.newContext({ baseURL });
    try {
      // Wargor holds "admin", which getUserRights expands to "moderator", so the
      // ownership check in supprCreation.php passes whichever browser identity
      // originally created the track.
      const auth = await ctx.post('/api/testcode.php', { form: { pseudo: ADMIN_USER, code: ADMIN_PASSWORD } });
      const body = (await auth.text()).trim();
      if (!(Number(body) > 0))
        throw new Error(
          'cleanup could not log in as ' + ADMIN_USER + ' (testcode.php returned "' + body + '"). ' +
          'Check the seeded credentials and that baseURL points at the site under test.'
        );
      // Batched because a first run against an existing backlog is otherwise slow
      // enough to exhaust the hook timeout (40 deletes: 144ms serially, 32ms here).
      const CONCURRENCY = 10;
      for (let i = 0; i < circuits.length; i += CONCURRENCY) {
        await Promise.all(
          circuits.slice(i, i + CONCURRENCY).map((id: string) =>
            ctx.post('/api/supprCreation.php', { form: { id, collab: '' } })
          )
        );
      }
    } finally {
      await ctx.dispose();
    }
  }
  // Cups and multicups whose tracks were already gone are never reached by the
  // cascade above, so sweep them by the same author scope.
  const p = TEST_AUTHOR_PATTERN;
  await sql('DELETE t FROM mkmcups_tracks t JOIN mkmcups m ON m.id = t.mcup WHERE m.auteur LIKE ?', [p]);
  await sql('DELETE FROM mkmcups WHERE auteur LIKE ?', [p]);
  await sql('DELETE FROM mkcups WHERE auteur LIKE ?', [p]);
  await deleteFollowerNotifs('circuits', circuits);
  await deleteFollowerNotifs('cups', cups);
  await deleteFollowerNotifs('mcups', mcups);

  // supprCreation.php echoes success even when it matched no row, so verify.
  const left: any = await sql(
    `SELECT
       (SELECT COUNT(*) FROM mkcircuits WHERE auteur LIKE ?) AS circuits,
       (SELECT COUNT(*) FROM mkcups     WHERE auteur LIKE ?) AS cups,
       (SELECT COUNT(*) FROM mkmcups    WHERE auteur LIKE ?) AS mcups`,
    [p, p, p]
  );
  const remaining = Object.entries(left[0]).filter(([, n]) => Number(n) > 0);
  if (remaining.length)
    throw new Error('cleanup left ' + remaining.map(([t, n]) => n + ' ' + t).join(', '));
}

export async function login(page: Page) {
  await page.goto('/');
  await page.getByRole('menuitem', { name: 'Forum' }).click();
  await page.getByLabel('Login:').fill(ADMIN_USER);
  await page.getByLabel('Password:').fill(ADMIN_PASSWORD);
  await page.getByRole('button', { name: 'Submit' }).click();
  // Land on a normal page so session.php establishes the identity cookie.
  // After login, setId.php pins the context's identifiant to Wargor's profile
  // identifiant (0 in the seed); the seeded mkidentifiants row (identifiant 0,
  // disable_cooldown=1) lets this bot skip the track-creation cooldown.
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
  opts: { author: string; name?: string; map?: string; laps?: string }
): Promise<number> {
  const form: Record<string, string> = {
    nom: opts.name ?? 'e2e-circuit',
    auteur: opts.author,
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
export async function createCircuits(request: APIRequestContext, count: number, author: string): Promise<number[]> {
  const ids: number[] = [];
  for (let i = 0; i < count; i++) {
    try {
      ids.push(await createCircuit(request, { name: 'e2e-circuit-' + (i + 1), author }));
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
  opts: { name: string; circuitIds: number[]; author: string; options?: object }
): Promise<number> {
  const form: Record<string, string> = { nom: opts.name, auteur: opts.author, mode: '0' };
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
  opts: { name: string; cupIds: number[]; author: string; options?: object }
): Promise<number> {
  const form: Record<string, string> = { nom: opts.name, auteur: opts.author, mode: '0' };
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
