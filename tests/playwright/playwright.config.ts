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
    launchOptions: {
      slowMo,
    },
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
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
