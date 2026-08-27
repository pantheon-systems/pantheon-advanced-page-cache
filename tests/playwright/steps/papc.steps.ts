import { createBdd } from 'playwright-bdd';
import { test, expect } from '../fixtures/papc-fixtures';
import type { APIResponse } from '@playwright/test';

const { Given, When, Then, Before } = createBdd(test);

let lastResponse: APIResponse;
let loggedIn = false;

// Pacing keeps the suite under the edge's per-IP request ceiling.
const PACE_MS = parseInt(process.env.SCENARIO_PACE_MS || '3000');

Before(async () => {
  loggedIn = false;
  if (PACE_MS > 0) await new Promise((r) => setTimeout(r, PACE_MS));
});

// Session comes from storageState; this only flips the browser-vs-API routing flag.
Given('I log in as an admin', async () => {
  loggedIn = true;
});

// Without login this is an API request; after login it is a browser navigation.
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
  // A missing header is undefined, so report that rather than a value mismatch.
  expect(actual, `response header "${headerName}" is missing`).toBeDefined();
  expect(actual).toBe(expectedValue);
});
