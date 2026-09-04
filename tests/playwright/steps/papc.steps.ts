import { createBdd } from 'playwright-bdd';
import { test, expect } from '../fixtures/papc-fixtures';
import { SCENARIO_PACE_MS } from '../constants';
const { Given, When, Then, Before } = createBdd(test);

let firstScenario = true;

Before(async () => {
  // Space scenarios apart, but not before the first, which has nothing to follow.
  if (SCENARIO_PACE_MS > 0 && !firstScenario) await new Promise((r) => setTimeout(r, SCENARIO_PACE_MS));
  firstScenario = false;
});

// The admin session comes from storageState, set up once by the setup project.
// This step documents the precondition in Gherkin; it has nothing to do.
Given('I log in as an admin', async () => {});

// Drives a real browser. Use "I request" for scenarios asserting on headers.
Given('I go to {string}', async ({ page }, url: string) => {
  await page.goto(url, { waitUntil: 'load' });
});

// Plain HTTP, no browser. Carries the debug headers the surrogate-key tests need.
Given('I request {string}', async ({ pantheonAPI, scenario }, url: string) => {
  scenario.lastResponse = await pantheonAPI.get(url);
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

// The plugins screen lists installed plugins whether active or not; only the
// row's class distinguishes the two.
Then('the {string} plugin should be active', async ({ page }, pluginFile: string) => {
  await expect(page.locator(`tr[data-plugin="${pluginFile}"]`)).toHaveClass(/(^|\s)active(\s|$)/);
});

Then('the {string} field should contain {string}', async ({ page }, fieldName: string, expectedValue: string) => {
  await expect(page.locator(`[name="${fieldName}"]`)).toHaveValue(expectedValue);
});

Then('the response header {string} should be {string}', async ({ scenario }, headerName: string, expectedValue: string) => {
  const response = scenario.lastResponse;
  if (!response) throw new Error('No response recorded. This scenario needs an "I request" step.');
  // A missing header reads as undefined, which names the real problem.
  const actual = response.headers()[headerName.toLowerCase()];
  expect(actual, `response header "${headerName}"`).toBe(expectedValue);
});
