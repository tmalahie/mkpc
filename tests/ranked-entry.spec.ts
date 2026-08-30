import { test, expect } from '@playwright/test';

// Entering ranked while logged out should land on the online page, which logs the visitor
// in without navigating away, rather than bouncing them to the forum.
test.describe.configure({ mode: 'serial' });

test('ranked entry sends a logged-out visitor to the online page', async ({ page }) => {
	const res = await page.request.get('http://127.0.0.1:8080/ranked.php', { maxRedirects: 0 });
	expect(res.status()).toBe(302);
	expect(res.headers()['location']).toMatch(/^online\.php\?mid=\d+&ranked$/);
});

// Creating a multicup here would need two new tracks, and the creation cooldown is
// keyed on the caller's IP - so a whole suite run shares one budget of 2 per minute.
// online.php?battle needs no fixture and still carries a query string, which is the
// part of the return URL that has to survive.
test('the register link comes back to the online page it was opened from', async ({ browser }) => {
	test.setTimeout(60000);
	const guest = await browser.newContext();
	const guestPage = await guest.newPage();
	await guestPage.goto('http://127.0.0.1:8080/online.php?battle', { waitUntil: 'domcontentloaded' });

	const register = guestPage.locator('a[href^="inscription.php"]');
	await expect(register.first()).toBeAttached({ timeout: 30000 });
	expect(await register.first().getAttribute('href')).toBe('inscription.php?online.php?battle');

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
