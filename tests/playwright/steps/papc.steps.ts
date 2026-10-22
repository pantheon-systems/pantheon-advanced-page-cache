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

// Plain HTTP, no browser ("I visit" drives the browser). Carries the debug headers the surrogate-key tests need.
Given('I request {string}', async ({ pantheonAPI, scenario }, url: string) => {
  scenario.lastResponse = await pantheonAPI.get(url);
});

When('I fill in the field named {string} with {string}', async ({ page }, fieldName: string, value: string) => {
  const field = page.locator(`[name="${fieldName}"]`);
  // Shared "I fill in" matches by label, and this select has none. fill() rejects <select>, which Behat's Mink handled transparently.
  const tag = await field.evaluate((el) => el.tagName.toLowerCase());
  if (tag === 'select') {
    await field.selectOption(value);
  } else {
    await field.fill(value);
  }
});

// The plugins screen lists installed plugins whether active or not; only the
// row's class distinguishes the two.
Then('the {string} plugin should be active', async ({ page }, pluginFile: string) => {
  await expect(page.locator(`tr[data-plugin="${pluginFile}"]`)).toHaveClass(/(^|\s)active(\s|$)/);
});

Then('the field named {string} should contain {string}', async ({ page }, fieldName: string, expectedValue: string) => {
  await expect(page.locator(`[name="${fieldName}"]`)).toHaveValue(expectedValue);
});

Then('the response header {string} should be {string}', async ({ scenario }, headerName: string, expectedValue: string) => {
  const response = scenario.lastResponse;
  if (!response) throw new Error('No response recorded. This scenario needs an "I request" step.');
  // A missing header reads as undefined, which names the real problem.
  const actual = response.headers()[headerName.toLowerCase()];
  expect(actual, `response header "${headerName}"`).toBe(expectedValue);
});
