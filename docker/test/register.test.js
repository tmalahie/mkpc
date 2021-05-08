const puppeteer = require('puppeteer')
require('pptr-testing-library/extend')
const { queries } = require('pptr-testing-library')
const { getByText } = queries

const BASE_HREF = "http://localhost:8080";
let browser, page;

beforeAll(async () => {
    browser = await puppeteer.launch();
    page = await browser.newPage();
    await page.setExtraHTTPHeaders({
        'Accept-Language': 'en'
    });
});

test('Should go to the forum', async () => {
    await page.goto(BASE_HREF);
    const $nav = await page.$("nav");
    const $forum = await getByText($nav, "Forum");
    await $forum.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toBe(BASE_HREF+"/forum.php");
});

test('Should go to the register page', async () => {
    const $document = await page.getDocument();
    const $register = await $document.getByText("Register");
    await $register.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toBe(BASE_HREF+"/signup.php");
});

test('Should register', async () => {
    const $document = await page.getDocument();
    const $nick = await $document.getByLabelText(/choose a nick/i);
    await $nick.evaluate(nick => nick.value = "Wargor");
    const $password = await $document.getByLabelText(/choose a password/i);
    await $password.evaluate(password => password.value = "aaaa");
    const $confirm = await $document.getByLabelText(/re-enter password/i);
    await $confirm.evaluate(confirm => confirm.value = "aaaa");
    const $email = await $document.getByLabelText(/email address/i);
    await $email.evaluate(email => email.value = "email@example.com");
    const $country = await $document.getByLabelText(/country/i);
    await $country.evaluate(country => country.value = "fr");

    const $submit = await $document.getByRole("button", {name: "Submit"});
    await $submit.evaluate(submit => submit.form.submit());

    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const $document2 = await page.getDocument();
    await $document2.getByText("Welcome", {exact:false});
    const $back = await $document2.getByRole("link", {name: "Back to the forum"});
    await $back.click();

    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toBe(BASE_HREF+"/forum.php");
});

test('Should go to my profile', async () => {
    const $document = await page.getDocument();
    const $profile = await $document.getByRole("link", {name: "My profile"});
    await $profile.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toMatch(BASE_HREF+"/profil.php");
});

test('Should have correct information', async () => {
    const $document = await page.getDocument();
    await $document.getByText("Wargor's profile");
    const $back = await $document.getByRole("link", {name: "Back to the forum", exact: false});
    await $back.click();

    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toBe(BASE_HREF+"/forum.php");
});

test('Should log out', async () => {
    const $document = await page.getDocument();
    const $logout = await $document.getByRole("link", {name: "Log out"});
    await $logout.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toMatch(BASE_HREF+"/forum.php");
});

test('Should relog in', async () => {
    const passwordsAttempts = ["aaaaaa", "aaaa"];
    for (const passwordsAttempt of passwordsAttempts) {
        const $document = await page.getDocument();
        const $login = await $document.getByLabelText(/login/i);
        await $login.evaluate(login => login.value = "Wargor");
        const $password = await $document.getByLabelText(/password/i);
        await $password.evaluate((password,passwordsAttempt) => password.value = passwordsAttempt, passwordsAttempt);

        await $password.evaluate(password => password.form.submit());

        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        const url = await page.url();
        expect(url).toMatch(BASE_HREF+"/forum.php");
    }

    const $document2 = await page.getDocument();
    await $document2.getByRole("link", {name: "My profile"});
});

afterAll(async () => {
    await page.close();
    await browser.close();
});