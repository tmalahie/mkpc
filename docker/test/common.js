const fs = require('fs').promises;
const path = require('path');
const { sleep } = require("./utils.js");
require("expect-puppeteer");
beforeAll(async () => {
    await page.setExtraHTTPHeaders({
        'Accept-Language': 'en'
    });
    if (!testId) {
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