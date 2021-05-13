const fs = require('fs').promises;
const path = require('path');
require("expect-puppeteer");
beforeAll(async () => {
    if (!testId) {
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'en'
        });
        page.on('pageerror', ({ message }) => console.error(message))
        const directory = "test-screenshots";
        const files = await fs.readdir(directory);
        for (const file of files) {
            if (file.startsWith("test"))
                await fs.unlink(path.join(directory, file));
        }
    }
});

var testId = 0;
afterEach(async () => {
    testId++;
    await page.screenshot({path: `test-screenshots/test${testId}.png`}).catch(() => {});
});

module.exports = {
    BASE_HREF: "http://localhost:8080"
};