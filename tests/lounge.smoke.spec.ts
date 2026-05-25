import { test, expect } from '@playwright/test';

async function login(page) {
	const res = await page.request.post('http://127.0.0.1:8080/api/testcode.php', {
		form: { pseudo: 'wargor', code: 'aaaa' },
	});
	const body = await res.text();
	expect(Number(body)).toBeGreaterThan(0);
	const cookies = res.headersArray().filter(h => h.name.toLowerCase() === 'set-cookie');
	for (const c of cookies) {
		const [pair] = c.value.split(';');
		const [name, value] = pair.split('=');
		await page.context().addCookies([{ name, value, domain: '127.0.0.1', path: '/' }]);
	}
}

test('lounge page renders tiers for logged-in user', async ({ page }) => {
	await login(page);
	await page.goto('http://127.0.0.1:8080/lounge.php');

	await expect(page.locator('.lounge-header h1')).toHaveText('CT Lounge');
	await expect(page.locator('.lounge-tier').first()).toBeVisible({ timeout: 5000 });

	const tiers = page.locator('.lounge-tier');
	await expect(tiers).toHaveCount(5);

	await expect(tiers.nth(0).locator('.lounge-tier-title')).toContainText('All');
	await expect(tiers.nth(1).locator('.lounge-tier-title')).toContainText('C');
	await expect(tiers.nth(2)).toHaveClass(/is-locked/);
});

test('lounge tab switching works', async ({ page }) => {
	await login(page);
	await page.goto('http://127.0.0.1:8080/lounge.php');

	await expect(page.locator('[data-panel="queueup"]')).toHaveClass(/is-active/);
	await page.locator('[data-tab="howitworks"]').click();
	await expect(page.locator('[data-panel="howitworks"]')).toHaveClass(/is-active/);
	await expect(page.locator('[data-panel="queueup"]')).not.toHaveClass(/is-active/);
});

test('lounge page is gated when logged out', async ({ page }) => {
	await page.goto('http://127.0.0.1:8080/lounge.php');
	await expect(page.locator('.lounge-gate')).toBeVisible();
	await expect(page.locator('#lounge')).toHaveCount(0);
});

test('Ranked button opens the lounge overlay from online.php', async ({ page }) => {
	test.setTimeout(60000);
	await login(page);
	await page.goto('http://127.0.0.1:8080/online.php', { waitUntil: 'domcontentloaded' });
	await page.waitForFunction(() => typeof window['openLoungeOverlay'] === 'function', null, { timeout: 30000 });
	await page.evaluate(() => window['openLoungeOverlay']());

	const overlay = page.locator('#lounge-overlay');
	await expect(overlay).toBeVisible();
	const frame = page.frameLocator('#lounge-overlay iframe');
	await expect(frame.locator('.lounge-header h1')).toHaveText('CT Lounge');
});
