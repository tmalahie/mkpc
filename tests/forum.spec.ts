import { test, expect } from '@playwright/test';
import { randomBytes } from 'crypto';
import { newTopicName } from './helpers/forum';

const ADMIN_USER = 'wargor';
const ADMIN_PASSWORD = 'aaaa';
const ADMIN_USERNAME_PRINTED = "Wargor";

// Named so tests/global-cleanup.ts recognises and removes it afterwards.
const TOPIC_NAME = newTopicName();
const TOPIC_CONTENT = "New topic content " + randomBytes(10).toString('hex');

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
