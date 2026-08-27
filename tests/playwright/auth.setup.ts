import { test as setup, expect } from '@playwright/test';

setup('authenticate as admin', async ({ page }) => {
  const user = process.env.WP_USER;
  const password = process.env.WP_PASSWORD;
  if (!user || !password) throw new Error('WP_USER and WP_PASSWORD are required');

  await page.goto('/wp-login.php', { waitUntil: 'load' });
  await page.locator('#user_login').fill(user);
  await page.locator('#user_pass').fill(password);
  await page.locator('#wp-submit').click();

  await expect(page.locator('#wpadminbar')).toBeVisible({ timeout: 30000 });

  await page.context().storageState({ path: process.env.AUTH_FILE! });
});
