import { test as setup, expect } from '@playwright/test';
import { AUTH_FILE } from './auth-file';

// Authenticate once. A Gherkin Background runs per scenario, so a login step there costs one login each.
setup('authenticate as admin', async ({ page }) => {
  const user = process.env.WP_USER;
  const password = process.env.WP_PASSWORD;
  if (!user || !password) throw new Error('WP_USER and WP_PASSWORD are required');

  await page.goto('/wp-login.php', { waitUntil: 'load' });
  await page.locator('#user_login').fill(user);
  await page.locator('#user_pass').fill(password);
  await page.locator('#wp-submit').click();

  // Fail here rather than in every scenario if the credentials or the site are wrong.
  await expect(page.locator('#wpadminbar')).toBeVisible({ timeout: 30000 });

  await page.context().storageState({ path: AUTH_FILE });
});
