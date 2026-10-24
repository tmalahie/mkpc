import { test, expect, Page } from '@playwright/test';
import { createCircuit } from './helpers/mkpc';
import {
  CHALLENGER, ensureChallenger, playerId, loginAs, challengesOf, publisherOf, creatorWins, finishRaceOn,
} from './helpers/challenges';

// The challenge lifecycle as members and validators go through it: create and edit
// a challenge in the editor, prove it in game, publish it, get it rejected then
// accepted by a validator, read the moderation result, delete it. The collab test
// covers a track collab link with the "manage challenges" right.

const ADMIN_USER = 'wargor';
const ADMIN_PASSWORD = 'aaaa';

let challengerId: number;
let adminId: number;

test.beforeAll(async () => {
  challengerId = await ensureChallenger();
  adminId = await playerId(ADMIN_USER);
});

async function createChallenge(page: Page, listUrl: string, name: string) {
  await page.goto(listUrl);
  await page.getByRole('link', { name: /Create my first challenge/ }).click();
  await page.locator('#challenge-main-rule').selectOption('finish_circuit');
  await page.getByLabel('Challenge name (optional):').fill(name);
  await page.getByLabel('Difficulty:').selectOption('0');
  await page.getByRole('button', { name: 'Confirm!' }).click();
  await expect(page.locator('body')).toContainText('Your challenge has been created');
}

async function publish(page: Page, listUrl: string, expectedMessage: string) {
  await page.goto(listUrl);
  await page.getByRole('link', { name: 'Publish now' }).click();
  const confirm = page.locator('.o_dialog_box .o_dialog_submit');
  const message = page.locator('body', { hasText: expectedMessage });
  await expect(confirm.or(message).first()).toBeVisible();
  if (await confirm.isVisible())
    await confirm.click();
  await expect(page.locator('body')).toContainText(expectedMessage);
}

async function moderate(page: Page, challengeId: number, action: 'accept' | 'reject', msg?: string) {
  await page.goto('/challengesList.php?moderate&author=' + challengerId);
  const item = page.locator('#challenges-item-' + challengeId);
  await item.locator('.challenges-item-' + action).click();
  if (msg)
    await page.locator('.o_dialog_box input[type=text]').fill(msg);
  const moderated = page.waitForResponse((res) => res.url().includes('/api/challengeModerate.php'));
  await page.locator('.o_dialog_box .o_dialog_submit').click();
  expect((await moderated).ok()).toBe(true);
}

test('a challenge goes from creation to moderation and deletion', async ({ browser }) => {
  test.setTimeout(60_000);
  const author = await (await browser.newContext()).newPage();
  const validator = await (await browser.newContext()).newPage();
  await loginAs(author.request, CHALLENGER.name, CHALLENGER.password);
  await loginAs(validator.request, ADMIN_USER, ADMIN_PASSWORD);
  const circuitId = await createCircuit(author.request, { author: 'e2e-challenges' });
  const trackUrl = '/circuit.php?id=' + circuitId;
  let listUrl = '/challenges.php?page=circuit&id=' + circuitId;
  let challenge: any;

  await test.step('create a challenge in the editor', async () => {
    await createChallenge(author, listUrl, 'e2e first name');
    [challenge] = await challengesOf(circuitId);
    expect(challenge).toMatchObject({ name: 'e2e first name', status: 'pending_completion', identifiant: CHALLENGER.identifiant });
    listUrl = '/challenges.php?cl=' + challenge.clist;
  });

  await test.step('edit it', async () => {
    await author.goto(listUrl);
    await author.locator('.challenge-action-edit').click();
    await author.getByLabel('Challenge name (optional):').fill('e2e renamed');
    await author.getByRole('button', { name: 'Confirm!' }).click();
    await expect(author.locator('body')).toContainText('Your challenge has been edited');
    await expect(author.locator('body')).toContainText('e2e renamed');
  });

  await test.step('prove it by completing the track', async () => {
    await finishRaceOn(author, trackUrl);
    expect((await challengesOf(circuitId))[0].status).toBe('pending_publication');
    expect(await creatorWins(challenge.id)).toEqual([challengerId]);
  });

  await test.step('publish it', async () => {
    await publish(author, listUrl, 'Your publication request has been taken into account');
    expect((await challengesOf(circuitId))[0].status).toBe('pending_moderation');
    expect(await publisherOf(challenge.id)).toBe(challengerId);
  });

  await test.step('a validator rejects it, and the author reads why', async () => {
    await moderate(validator, challenge.id, 'reject', 'e2e: too easy');
    expect((await challengesOf(circuitId))[0].status).toBe('pending_completion');
    await author.goto('/challengeDetails.php?ch=' + challenge.id);
    await expect(author.locator('body')).toContainText('your challenge has been rejected');
    await expect(author.locator('body')).toContainText('e2e: too easy');
  });

  await test.step('proved and published again, a validator accepts it', async () => {
    await finishRaceOn(author, trackUrl);
    await publish(author, listUrl, 'Your publication request has been taken into account');
    await moderate(validator, challenge.id, 'accept');
    expect((await challengesOf(circuitId))[0].status).toBe('active');
    await author.goto('/challengeDetails.php?ch=' + challenge.id);
    await expect(author.locator('body')).toContainText('your challenge has been accepted');
  });

  await test.step('delete it', async () => {
    await author.goto(listUrl);
    author.once('dialog', (dialog) => dialog.accept());
    await author.locator('.challenge-action-del').click();
    await author.waitForURL(/challenges\.php/);
    expect(await challengesOf(circuitId)).toEqual([]);
    expect(await publisherOf(challenge.id)).toBeUndefined();
  });
});

test('a collab link lets another member manage the track challenges', async ({ browser }) => {
  const owner = await (await browser.newContext()).newPage();
  const collaborator = await (await browser.newContext()).newPage();
  await loginAs(owner.request, CHALLENGER.name, CHALLENGER.password);
  await loginAs(collaborator.request, ADMIN_USER, ADMIN_PASSWORD);
  const circuitId = await createCircuit(owner.request, { author: 'e2e-challenges-collab' });

  let collabKey = '';
  await test.step('the owner creates a link with the "manage challenges" right', async () => {
    await owner.goto('/circuit.php?id=' + circuitId);
    await owner.locator('#linkRace').click();
    await owner.getByLabel("Manage the circuit's challenges").check();
    await owner.getByRole('button', { name: 'Create share link' }).click();
    const link = owner.locator('.collab-popup-success a');
    await expect(link).toHaveText(/collab=/);
    collabKey = new URL((await link.textContent())!).searchParams.get('collab')!;
  });

  const listUrl = '/challenges.php?page=circuit&id=' + circuitId + '&collab=' + collabKey;
  let challenge: any;

  await test.step('the collaborator creates a challenge, owned by the track owner', async () => {
    await createChallenge(collaborator, listUrl, 'e2e collab challenge');
    [challenge] = await challengesOf(circuitId);
    expect(challenge.identifiant).toBe(CHALLENGER.identifiant);
  });

  await test.step('without the key, the challenge stays out of reach', async () => {
    await collaborator.goto('/challenges.php?cl=' + challenge.clist);
    await expect(collaborator.locator('body')).not.toContainText('e2e collab challenge');
  });

  await test.step('the collaborator proves and publishes it', async () => {
    await finishRaceOn(collaborator, '/circuit.php?id=' + circuitId + '&collab=' + collabKey);
    expect(await creatorWins(challenge.id)).toEqual([adminId]);
    await publish(collaborator, '/challenges.php?cl=' + challenge.clist + '&collab=' + collabKey, 'The challenge has been published');
    expect((await challengesOf(circuitId))[0].status).toBe('active');
    expect(await publisherOf(challenge.id)).toBe(adminId);
  });
});
