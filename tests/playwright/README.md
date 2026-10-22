# End-to-end tests

Playwright BDD tests built on [cms-bdd](https://github.com/pantheon-systems/cms-bdd). They run
against a Pantheon multidev with the plugin deployed, not a local WordPress.

| Path | What it holds |
|---|---|
| `features/` | Gherkin scenarios |
| `steps/` | PAPC-specific step definitions. Generic browser steps come from cms-bdd |
| `fixtures/` | `pantheonAPI` for header assertions, `scenario` for per-test state |
| `auth.setup.ts` | Logs in once and saves the session |

Two navigation steps: `I visit` (from cms-bdd) drives a browser, `I request` issues plain HTTP.
Scenarios asserting on response headers need `I request`.

`I visit`, `I press` and `I should see` also come from cms-bdd, which `playwright.config.ts` loads from
`node_modules/cms-bdd/dist/steps`. `I should see` needs the text to be *visible*, so a result inside
a collapsed panel has to be expanded first. Define a step here only if cms-bdd lacks it, and
never with the same text as a shared step: playwright-bdd fails on duplicates.

The settings screen's Max Age `<select>` has no label, so `I fill in the field named` and
`the field named ... should contain` locate it by `name` and stay local until cms-bdd's
name fallback is released (pantheon-systems/cms-bdd#20).

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
npm install "cms-bdd@git+https://github.com/pantheon-systems/cms-bdd.git#<sha>"
```

Use that explicit URL, not the `github:owner/repo` shorthand. npm rewrites the shorthand to
`git+ssh://` in the lockfile, which makes every install authenticate over SSH even though
cms-bdd is public.

Commit `package.json` and `package-lock.json` together. Keep `@playwright/test`, `playwright`
and `playwright-bdd` on their exact pinned versions and move them together.

`@playwright/test`, `playwright`, `playwright-bdd` and `allure-playwright` are peer dependencies
of cms-bdd (1.0.x needs playwright-bdd 9; `allure-playwright` is held at 3.10.2 because 3.14 needs Playwright 1.62), so they are declared here rather than coming in with it. Playwright's runner has to
resolve a single copy: nested copies mean the fixtures register against a different instance
than the one running the tests, and `bddgen` runs from this repo.
