import { defineConfig, devices } from '@playwright/test';
import { defineBddConfig } from 'playwright-bdd';
import * as dotenv from 'dotenv';

dotenv.config();

if (!process.env.WP_URL) {
  throw new Error('WP_URL environment variable is required. Set it in .env or as an environment variable.');
}

const testDir = defineBddConfig({
  featuresRoot: '../',
  features: '../behat/**/*.feature',
  steps: [
    'steps/**/*.ts',
    'fixtures/**/*.ts',
    'node_modules/cms-bdd/dist/fixtures/customFixtures.js',
  ],
});

const headless = process.env.HEADLESS !== 'false';
const slowMo = parseInt(process.env.SLOW_MO || '0');

export default defineConfig({
  testDir,
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: [
    ['html'],
    ['list'],
  ],
  timeout: 300000,
  use: {
    baseURL: process.env.WP_URL,
    headless,
    // Headless Chromium's default User-Agent gets served a Cloudflare "Just a
    // moment..." challenge page on wp-login.php instead of the login form.
    // A normal desktop Chrome UA string is enough to avoid it -- confirmed
    // locally against the live site (EDRT-9550): default UA -> challenge page,
    // this UA -> real login page. Matches Conor Bauer's diagnosis for Behat's
    // Goutte driver, which is flagged the same way for the same reason.
    userAgent:
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    launchOptions: {
      slowMo,
    },
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    // 'on' is for this SITE-5879 spike/PoC only, to prove the capability exists --
    // the acceptance criterion is "test execution is recorded as a video". Once
    // this is permanent CI (not evaluation), switch back to 'retain-on-failure':
    // recording every passing run forever, across every repo, is artifact storage
    // cost with no ongoing debugging benefit.
    video: 'on',
    actionTimeout: 10000,
    navigationTimeout: 30000,
  },
  projects: [
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        viewport: { width: 1920, height: 1080 },
      },
    },
  ],
});
