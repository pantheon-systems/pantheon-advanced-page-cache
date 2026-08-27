import { test as cmsBddTest, expect } from 'cms-bdd';
import { APIRequestContext, APIResponse, request } from '@playwright/test';
import { API_UA } from '../constants';

type PAPCFixtures = {
  pantheonAPI: APIRequestContext;
  scenario: { lastResponse?: APIResponse };
};

export const test = cmsBddTest.extend<PAPCFixtures>({
  // Per-test state. A module-level variable would persist between scenarios in
  // the same worker; a fixture is rebuilt for each one.
  scenario: async ({}, use) => {
    await use({});
  },
  pantheonAPI: async ({}, use) => {
    const baseURL = process.env.WP_URL;
    if (!baseURL) throw new Error('WP_URL not set');
    const ctx = await request.newContext({
      baseURL,
      ...API_UA,
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
