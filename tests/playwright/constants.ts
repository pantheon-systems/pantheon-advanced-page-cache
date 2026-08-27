import path from 'path';

export const AUTH_FILE = path.join(__dirname, '.auth/admin.json');

export const SCENARIO_PACE_MS = parseInt(process.env.SCENARIO_PACE_MS || '3000', 10);

// Allowlisted CI User-Agent, supplied as a secret. Apply after any devices
// spread, which sets a userAgent of its own.
export const BROWSER_UA = process.env.CI_UA ? { userAgent: process.env.CI_UA } : {};

// A node-prefixed agent keeps API calls in a more permissive edge tier.
export const API_UA = process.env.CI_UA ? { userAgent: `node ${process.env.CI_UA}` } : {};
