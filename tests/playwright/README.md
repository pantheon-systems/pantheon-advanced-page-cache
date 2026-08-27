# End-to-end tests

Playwright BDD tests built on [cms-bdd](https://github.com/pantheon-systems/cms-bdd). They run
against a Pantheon multidev with the plugin deployed, not a local WordPress.

| Path | What it holds |
|---|---|
| `features/` | Gherkin scenarios |
| `steps/` | Step definitions |
| `fixtures/` | `pantheonAPI` for header assertions, `scenario` for per-test state |
| `auth.setup.ts` | Logs in once and saves the session |

Two navigation steps: `I go to` drives a browser, `I request` issues plain HTTP. Scenarios
asserting on response headers need `I request`.

## Running it

```bash
cp .env.example .env   # fill in WP_URL, WP_USER, WP_PASSWORD
npm ci
npx playwright install --with-deps chrome
npm test
```

`npx playwright show-report` opens the last run. Note that API responses are not in the page
trace, since `pantheonAPI` is a separate request context.

## Before changing the config

`workers`, `SCENARIO_PACE_MS`, the single login in `auth.setup.ts`, and the User-Agent handling
are all tuned to keep the suite under an edge request limit. They are deliberate, and adding
scenarios may mean retuning them. See SITE-5879 before changing any of them.

## Updating cms-bdd

`package.json` pins an exact commit and Dependabot does not raise PRs for git dependencies, so
this is manual:

```bash
npm install cms-bdd@github:pantheon-systems/cms-bdd#<sha>
```

Commit `package.json` and `package-lock.json` together. Keep `@playwright/test`, `playwright`
and `playwright-bdd` on their exact pinned versions and move them together.
