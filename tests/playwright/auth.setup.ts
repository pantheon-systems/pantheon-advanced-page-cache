import { test as setup, expect } from '@playwright/test';
import { WPLoginPage } from 'cms-bdd';

setup('authenticate as admin', async ({ page }) => {
  const loginPage = new WPLoginPage(page);
  await loginPage.login();
  expect(await loginPage.isLoggedIn(), 'admin login failed').toBe(true);
  await page.context().storageState({ path: process.env.AUTH_FILE! });
});
