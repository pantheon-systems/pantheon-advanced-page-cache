import { defineConfig, devices } from '@playwright/test';
import { defineBddConfig } from 'playwright-bdd';
import * as dotenv from 'dotenv';

dotenv.config();

if (!process.env.WP_URL) {
  throw new Error('WP_URL environment variable is required. Set it in .env or as an environment variable.');
}

const testDir = defineBddConfig({
  featuresRoot: '.',
  features: 'features/**/*.feature',
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
    // PoC-only, to prove the capability. Revert to 'retain-on-failure' for permanent CI.
    video: 'on',
    actionTimeout: 10000,
    navigationTimeout: 30000,
  },
  projects: [
    {
      name: 'chrome',
      use: {
        ...devices['Desktop Chrome'],
        // Real Chrome, not bundled Chromium: different TLS fingerprint and build signature.
        channel: 'chrome',
        viewport: { width: 1920, height: 1080 },
        // Must come after the devices spread, which sets its own userAgent.
        ...(process.env.CI_UA ? { userAgent: process.env.CI_UA } : {}),
      },
    },
  ],
});
