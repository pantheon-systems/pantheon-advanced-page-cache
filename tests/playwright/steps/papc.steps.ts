import { createBdd } from 'playwright-bdd';
import { test, expect } from '../fixtures/papc-fixtures';
import type { APIResponse } from '@playwright/test';

const { Given, When, Then, Before } = createBdd(test);

let lastResponse: APIResponse;
let loggedIn = false;

Before(async () => {
  loggedIn = false;
});

Given('I log in as an admin', async ({ wpLoginPage }) => {
  await wpLoginPage.login();
  loggedIn = true;
});

// Surrogate key features use this without login (API request with Pantheon-Debug header).
// Admin features use this after login (browser navigation).
Given('I go to {string}', async ({ pantheonAPI, page }, url: string) => {
  if (loggedIn) {
    await page.goto(url, { waitUntil: 'load', timeout: 30000 });
  } else {
    lastResponse = await pantheonAPI.get(url);
  }
});

When('I fill in {string} with {string}', async ({ page }, fieldName: string, value: string) => {
  const field = page.locator(`[name="${fieldName}"]`);
  // fill() rejects <select>, which Behat's Mink handled transparently.
  const tag = await field.evaluate((el) => el.tagName.toLowerCase());
  if (tag === 'select') {
    await field.selectOption(value);
  } else {
    await field.fill(value);
  }
});

When('I press {string}', async ({ page }, buttonText: string) => {
  await page.getByRole('button', { name: buttonText }).click();
  await page.waitForLoadState('load');
});

Then('I should see {string}', async ({ page }, text: string) => {
  await expect(page.locator('body')).toContainText(text);
});

Then('the {string} field should contain {string}', async ({ page }, fieldName: string, expectedValue: string) => {
  await expect(page.locator(`[name="${fieldName}"]`)).toHaveValue(expectedValue);
});

Then('the response header {string} should be {string}', async ({}, headerName: string, expectedValue: string) => {
  const actual = lastResponse.headers()[headerName.toLowerCase()];
  // TEMPORARY diagnostic while the surrogate-key headers are under investigation. REVERT BEFORE MERGE.
  if (actual === undefined) {
    console.log(`[diag] ${lastResponse.url()} -> ${lastResponse.status()} ${lastResponse.statusText()}`);
    console.log(`[diag] headers: ${JSON.stringify(lastResponse.headers(), null, 2)}`);
  }
  // A missing header is undefined, so report that rather than a value mismatch.
  expect(actual, `response header "${headerName}" is missing`).toBeDefined();
  expect(actual).toBe(expectedValue);
});

Then('the response header {string} should not be {string}', async ({}, headerName: string, unexpectedValue: string) => {
  const actual = lastResponse.headers()[headerName.toLowerCase()];
  expect(actual, `response header "${headerName}" is missing`).toBeDefined();
  expect(actual).not.toBe(unexpectedValue);
});
