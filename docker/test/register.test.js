const puppeteer = require('puppeteer');
const { getByText, getByLabelText, getByValue, getLinkByText } = require("./utils.js");

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
    const $forum = await getLinkByText($nav, "Forum");
    await $forum.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toBe(BASE_HREF+"/forum.php");
});

test('Should go to the register page', async () => {
    const $register = await getLinkByText(page, "Register");
    await $register.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toBe(BASE_HREF+"/signup.php");
});

test('Should register', async () => {
    const $nick = await getByLabelText(page, "Choose a nick", {exact: false});
    await $nick.evaluate(nick => nick.value = "Wargor");
    const $password = await getByLabelText(page, "Choose a password", {exact: false});
    await $password.evaluate(password => password.value = "aaaa");
    const $confirm = await getByLabelText(page, "Re-enter password", {exact: false});
    await $confirm.evaluate(confirm => confirm.value = "aaaa");
    const $email = await getByLabelText(page, "Email address", {exact: false});
    await $email.evaluate(email => email.value = "email@example.com");
    const $country = await getByLabelText(page, "Country", {exact: false});
    await $country.evaluate(country => country.value = "fr");

    const $submit = await getByValue(page, "Submit", {tag: "input"});
    await $submit.evaluate(submit => submit.form.submit());

    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    await getByText(page, "Welcome", {exact:false});
    const $back = await getLinkByText(page, "Back to the forum");
    await $back.click();

    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toBe(BASE_HREF+"/forum.php");
});

test('Should go to my profile', async () => {
    const $profile = await getLinkByText(page, "My profile");
    await $profile.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toMatch(BASE_HREF+"/profil.php");
});

test('Should have correct information', async () => {
    await getByText(page, "Wargor's profile");
    const $back = await getLinkByText(page, "Back to the forum");
    await $back.click();

    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toBe(BASE_HREF+"/forum.php");
});

test('Should log out', async () => {
    const $logout = await getLinkByText(page, "Log out");
    await $logout.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toMatch(BASE_HREF+"/forum.php");
});

test('Should relog in', async () => {
    const passwordsAttempts = ["aaaaaa", "aaaa"];
    for (const passwordsAttempt of passwordsAttempts) {
        const $login = await getByLabelText(page, "Login", {exact: false});
        await $login.evaluate(login => login.value = "Wargor");
        const $password = await getByLabelText(page, "Password", {exact: false});
        await $password.evaluate((password,passwordsAttempt) => password.value = passwordsAttempt, passwordsAttempt);

        await $password.evaluate(password => password.form.submit());

        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        const url = await page.url();
        expect(url).toMatch(BASE_HREF+"/forum.php");
    }

    await getLinkByText(page, "My profile");
});

afterAll(async () => {
    await page.close();
    await browser.close();
});