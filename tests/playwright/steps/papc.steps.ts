import { createBdd } from 'playwright-bdd';
import { test, expect } from '../fixtures/papc-fixtures';
const { Given, When, Then, Before } = createBdd(test);

const PACE_MS = parseInt(process.env.SCENARIO_PACE_MS || '0');
let firstScenario = true;

Before(async () => {
  // Space scenarios apart, but not before the first, which has nothing to follow.
  if (PACE_MS > 0 && !firstScenario) await new Promise((r) => setTimeout(r, PACE_MS));
  firstScenario = false;
});

// Session comes from storageState; this only flips the browser-vs-API routing flag.
Given('I log in as an admin', async ({ scenario }) => {
  scenario.loggedIn = true;
});

Given('I go to {string}', async ({ pantheonAPI, page, scenario }, url: string) => {
  if (scenario.loggedIn) {
    await page.goto(url, { waitUntil: 'load', timeout: 30000 });
  } else {
    scenario.lastResponse = await pantheonAPI.get(url);
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

Then('the response header {string} should be {string}', async ({ scenario }, headerName: string, expectedValue: string) => {
  const actual = scenario.lastResponse!.headers()[headerName.toLowerCase()];
  // A missing header is undefined, so report that rather than a value mismatch.
  expect(actual, `response header "${headerName}" is missing`).toBeDefined();
  expect(actual).toBe(expectedValue);
});
