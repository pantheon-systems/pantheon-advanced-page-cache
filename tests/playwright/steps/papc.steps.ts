import { createBdd } from 'playwright-bdd';
import { test, expect } from 'cms-bdd';

const { Given, When, Then } = createBdd(test);

When('I log in to the WordPress site', async ({ wpLoginPage }) => {
  await wpLoginPage.login();
});

When('I navigate to {string}', async ({ page }, url: string) => {
  await page.goto(url, { waitUntil: 'load', timeout: 30000 });
});

Then('I should see {string}', async ({ page }, text: string) => {
  await expect(page.locator('body')).toContainText(text);
});

Then('the URL should contain {string}', async ({ page }, text: string) => {
  expect(page.url()).toContain(text);
});
