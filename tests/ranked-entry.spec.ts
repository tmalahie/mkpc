import { test, expect } from '@playwright/test';
import { login as uiLogin, createCircuits, createCup, createMulticup } from './helpers/mkpc';

// Entering ranked while logged out should land on the online page, which logs the visitor
// in without navigating away, rather than bouncing them to the forum.
test.describe.configure({ mode: 'serial' });

test('ranked entry sends a logged-out visitor to the online page', async ({ page }) => {
	const res = await page.request.get('http://127.0.0.1:8080/ranked.php', { maxRedirects: 0 });
	expect(res.status()).toBe(302);
	expect(res.headers()['location']).toMatch(/^online\.php\?mid=\d+&ranked$/);
});

test('the register link comes back to the same ranked page', async ({ page, browser }) => {
	test.setTimeout(60000);

	// setup.sql seeds no multicups, so build one to have a page that actually renders
	await uiLogin(page);
	const circuitIds = await createCircuits(page.request, 2);
	const cupId = await createCup(page.request, { name: 'e2e-entry-cup', circuitIds });
	const mid = await createMulticup(page.request, { name: 'e2e-entry-mcup', cupIds: [cupId] });

	// a fresh context so the visitor is genuinely logged out
	const guest = await browser.newContext();
	const guestPage = await guest.newPage();
	await guestPage.goto('http://127.0.0.1:8080/online.php?mid=' + mid + '&ranked', {
		waitUntil: 'domcontentloaded',
	});

	const register = guestPage.locator('a[href^="inscription.php"]');
	await expect(register.first()).toBeAttached({ timeout: 30000 });
	const href = await register.first().getAttribute('href');
	expect(href).toBe('inscription.php?online.php?mid=' + mid + '&ranked');

	await guest.close();
});

test('inscription returns to the page it was entered from', async ({ page }) => {
	const res = await page.request.get('http://127.0.0.1:8080/inscription.php?online.php?mid=15355&ranked');
	const html = await res.text();
	expect(html).toContain('href="online.php?mid=15355&amp;ranked"');
	// the target also survives a failed submission
	expect(html).toContain('action="inscription.php?online.php?mid=15355&amp;ranked"');
});

test('inscription still supports the plain battle entry point', async ({ page }) => {
	const res = await page.request.get('http://127.0.0.1:8080/inscription.php?battle');
	const html = await res.text();
	expect(html).toContain('href="online.php?battle"');
});

// A return target is a whole URL taken from the query string, so it must not be usable to
// bounce someone off-site.
for (const hostile of ['https://evil.com', '//evil.com', '../../etc/passwd', 'http:online.php']) {
	test(`inscription refuses the return target ${hostile}`, async ({ page }) => {
		const res = await page.request.get(
			'http://127.0.0.1:8080/inscription.php?' + hostile,
			{ maxRedirects: 0 }
		);
		const html = await res.text();
		expect(html).toContain('href="online.php"');
		expect(html).not.toContain('evil.com');
		expect(html).not.toContain('etc/passwd');
	});
}
