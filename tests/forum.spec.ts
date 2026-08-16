import { test, expect } from '@playwright/test';
import { randomBytes } from 'crypto';
import { sql } from './helpers/db';

const ADMIN_USER = 'wargor';
const ADMIN_PASSWORD = 'aaaa';
const ADMIN_USERNAME_PRINTED = "Wargor";

const RANDOM_ID = randomBytes(10).toString('hex');
const TOPIC_NAME = "New topic " + RANDOM_ID;
const TOPIC_CONTENT = "New topic content " + RANDOM_ID;

// Each run posts a topic under a fresh random suffix, so cleanup matches the
// shape of the generated title rather than one run's value: it clears this run's
// topic and any left behind by earlier ones. The anchored regexp keeps it from
// touching a real topic that merely starts with "New topic".
const TOPIC_PATTERN = '^New topic [0-9a-f]{20}$';

async function cleanupTopics() {
  await sql('DELETE m FROM mkmessages m JOIN mktopics t ON t.id = m.topic WHERE t.titre REGEXP ?', [TOPIC_PATTERN]);
  await sql('DELETE FROM mktopics WHERE titre REGEXP ?', [TOPIC_PATTERN]);
}

// afterAll is the contract, beforeAll is what protects this run: a killed run
// never reaches afterAll, and that is exactly when leftovers are created.
test.beforeAll(cleanupTopics);
test.afterAll(cleanupTopics);

test('logging in and creating a new topic', async ({ page }) => {
  // Log in
  await page.goto("/");
  await page.getByRole('menuitem', { name: 'Forum' }).click();
  await page.getByLabel('Login:').click();
  await page.getByLabel('Login:').fill(ADMIN_USER);
  await page.getByLabel('Login:').press('Tab');
  await page.getByLabel('Password:').fill(ADMIN_PASSWORD);
  await page.getByRole('button', { name: 'Submit' }).click();

  // Create a new topic
  await page.getByRole('link', { name: 'International forum' }).click();
  await page.getByRole('paragraph').filter({ hasText: /^New topic$/ }).getByRole('link', { name: 'New topic' }).click();
  await page.getByLabel('Title:').click();
  await page.getByLabel('Title:').fill(TOPIC_NAME);
  await page.getByLabel('Message:').click();
  await page.getByLabel('Message:').fill(TOPIC_CONTENT);
  await page.getByRole('button', { name: 'Send' }).click();

  // Try to open the topic just after posting it
  await page.getByRole('link', { name: 'Click here' }).first().click();

  // Check topic is as we expect it
  const topicUrl = page.url();
  await expect(page).toHaveTitle(new RegExp(TOPIC_NAME));
  await expect(page.getByRole("heading", { name: TOPIC_NAME, exact: true })).toBeVisible();

  // Check topic appears in the recent topics list
  await page.goto("/");
  await expect(page.locator('#forum_section')).toContainText(TOPIC_NAME);
  await expect(page.locator('#forum_section')).toContainText("Latest message by " + ADMIN_USERNAME_PRINTED);

  // Click on it and check we land on the same page
  await page.locator('#forum_section a').first().click();
  await expect(page.url()).toBe(topicUrl);
});
