import { test as cmsBddTest, expect } from 'cms-bdd';
import { APIRequestContext, APIResponse, request } from '@playwright/test';

type PAPCFixtures = {
  pantheonAPI: APIRequestContext;
  scenario: { loggedIn: boolean; lastResponse?: APIResponse };
};

export const test = cmsBddTest.extend<PAPCFixtures>({
  // Per-test state. Module-level variables would leak across workers.
  scenario: async ({}, use) => {
    await use({ loggedIn: false });
  },
  pantheonAPI: async ({}, use) => {
    const baseURL = process.env.WP_URL;
    if (!baseURL) throw new Error('WP_URL not set');
    const ctx = await request.newContext({
      baseURL,
      // A node-prefixed agent keeps these calls in a more permissive edge tier.
      ...(process.env.CI_UA ? { userAgent: `node ${process.env.CI_UA}` } : {}),
      extraHTTPHeaders: {
        // Both debug triggers are sent so the surrogate header survives either CDN.
        'Pantheon-Debug': '1',
        'Pantheon-SKey': '1',
      },
    });
    await use(ctx);
    await ctx.dispose();
  },
});

export { expect };
