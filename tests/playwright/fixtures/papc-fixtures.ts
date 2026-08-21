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
      extraHTTPHeaders: {
        // Cloudflare requires Pantheon-Debug exactly "1"; Fastly accepts either header.
        'Pantheon-Debug': '1',
        'Pantheon-SKey': '1',
      },
    });
    await use(ctx);
    await ctx.dispose();
  },
});

export { expect };
