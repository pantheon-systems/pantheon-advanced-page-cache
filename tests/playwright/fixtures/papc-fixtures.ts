import { test as cmsBddTest, expect } from 'cms-bdd';
import { APIRequestContext, request } from '@playwright/test';

type PAPCFixtures = {
  pantheonAPI: APIRequestContext;
};

export const test = cmsBddTest.extend<PAPCFixtures>({
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
