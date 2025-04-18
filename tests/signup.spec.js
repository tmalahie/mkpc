import { test, expect } from '@playwright/test';
import { randomBytes } from 'crypto';

const VALID_PASSWD = 'TESTTEST';
const VALID_NICK = `TestUser${randomBytes(4).toString('hex')}`;
const VALID_EMAIL = `test${randomBytes(4).toString('hex')}@example.com`;
const COUNTRY = 'United States';

// Helper function to fill out the signup form
async function fillSignupForm(page, username, password, reenteredPassword, email, country) {
    if (username) {
        await page.getByLabel('Choose a username :').fill(username);
    }
    if (password) {
        await page.getByLabel('Choose a password :').fill(password);
    }
    if (reenteredPassword) {
        await page.getByLabel('Re-enter password :').fill(reenteredPassword);
    }
    if (email) {
        await page.getByLabel('Email address (optional) :').fill(email);
    }
    if (country) {
        await page.getByLabel('Country (optional) :').selectOption(country);
    }
}

// Helper function to submit the form
async function submitSignupForm(page) {
    await Promise.all([
        page.waitForLoadState('load'),
        page.getByRole('button', { name: 'Submit' }).click(),
    ]);
}

// Helper function to check for error message
async function checkErrorMessage(page, errorMessage) {
    await expect(page.locator('#echec')).toHaveText(errorMessage);
}

// Test for Missing Username
test('signup with missing username', async ({ page }) => {
    await page.goto('/signup.php');
    await fillSignupForm(page, '', VALID_PASSWD, VALID_PASSWD, VALID_EMAIL, COUNTRY);
    await submitSignupForm(page);
    await checkErrorMessage(page, 'Please enter a username');
});

// Test for Missing Password
test('signup with missing password', async ({ page }) => {
    await page.goto('/signup.php');
    await fillSignupForm(page, VALID_NICK, '', '', VALID_EMAIL, COUNTRY);
    await submitSignupForm(page);
    await checkErrorMessage(page, 'Please choose a password');
});

// Test for Password Mismatch
test('signup with password mismatch', async ({ page }) => {
    await page.goto('/signup.php');
    await fillSignupForm(page, VALID_NICK, 'TestPassword123', 'DifferentPassword123', VALID_EMAIL, COUNTRY);
    await submitSignupForm(page);
    await checkErrorMessage(page, 'You made a mistake re-entering your password');
});

// Test for Password Too Short
test('signup with too short password', async ({ page }) => {
    await page.goto('/signup.php');
    await fillSignupForm(page, VALID_NICK, 'a', 'a', VALID_EMAIL, COUNTRY);
    await submitSignupForm(page);
    await checkErrorMessage(page, 'Your password must be at least 8 characters long.');
});

// Test for Invalid Username Format
test('signup with invalid username format', async ({ page }) => {
    await page.goto('/signup.php');
    await fillSignupForm(page, '!@#$%^d@Username', VALID_PASSWD, VALID_PASSWD, VALID_EMAIL, COUNTRY);
    await submitSignupForm(page);
    expect(page.getByText("You username mustn't contain special chars")).toBeVisible();
});

// Test for Invalid email Format
test('signup with invalid email format', async ({ page }) => {
    await page.goto('/signup.php');
    await fillSignupForm(page, VALID_NICK, VALID_PASSWD, VALID_PASSWD, 'invalidemilajas', COUNTRY);
    
    const initialFormState = await page.locator('form').innerHTML(); // Store initial state of the form

    await submitSignupForm(page);

    const newFormState = await page.locator('form').innerHTML(); // Check the form's state after the submission

    if (initialFormState !== newFormState) {
        throw new Error('page refreshed (form state changed) after submitting invalid email.');
    }
});

// Test for Username Already Used
test('signup with username already used', async ({ page }) => {
    await page.goto('/signup.php');
    await fillSignupForm(page, 'Wargor', VALID_PASSWD, VALID_PASSWD, VALID_EMAIL, COUNTRY);
    await submitSignupForm(page);
    await checkErrorMessage(page, 'This username is already used. Please choose another one.');
});

// Test for Valid Signup
test('signup with valid data', async ({ page }) => {
    await page.goto('/signup.php');
    await fillSignupForm(page, VALID_NICK, VALID_PASSWD, VALID_PASSWD, VALID_EMAIL, COUNTRY);
    await submitSignupForm(page);
    await expect(page.getByText('You are now registered ! Welcome to the Mario Kart community :)')).toBeVisible();
});
