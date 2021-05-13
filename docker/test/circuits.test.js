const { getByText, getByLabelText, getByValue, getLinkByText, getSingleResult, waitForScopedSelector, sleep } = require("./utils.js");
const { BASE_HREF } = require("./common.js");

test('Should create a circuit using quick track builder', async () => {
    await page.goto(BASE_HREF+"/create.php");
    await sleep(1000);
    const $submit = await getByValue(page, "Create circuit", {tag: "input", exact: false});
    await $submit.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toMatch(BASE_HREF+"/circuit.php");
});

test('Should share a circuit using quick track builder', async () => {
    const $share = await getByValue(page, "Share circuit", {tag: "input"});
    await $share.click();
    const $nick = await getByLabelText(page, "Enter your nick", {exact: false});
    await $nick.evaluate(nick => nick.value = "Wargor");
    const $name = await getByLabelText(page, "Circuit name", {exact: false});
    await $name.evaluate(name => name.value = "Test circuit");
    const $submit = await getByValue(page, "Share", {tag: "input"});
    await $submit.click();
    const $form = await $submit.evaluateHandle((submit) => submit.form);
    await waitForScopedSelector($form, 'input[value="Continue"]')
    const $continue = await $form.$$('input[value="Continue"]').then(getSingleResult);
    await $continue.click();
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    const url = await page.url();
    expect(url).toMatch(BASE_HREF+"/circuit.php");
});

test('Should see my circuit in creation list', async () => {
    const url = await page.url();
    const relativeUrl = url.replace(BASE_HREF+"/", "");
    await page.goto(BASE_HREF+"/creations.php");
    await page.$$('a[href="'+relativeUrl+'"]').then(getSingleResult);
});