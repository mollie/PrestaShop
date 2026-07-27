# Playwright E2E Migration — Design

**Status:** Approved for planning
**Author:** discovery session, 2026-07-27

## 1. Summary

Replace the Cypress E2E suite (`cypress/`, 27 files, ~1,300 LOC, PS8 only) with a
Playwright suite covering PS8 and PS1785, fixed for the redesigned React back-office
(`#mollie-authentication-root`, `#mollie-payment-methods-root`,
`#mollie-advanced-settings-root`), running risk-based rather than 1:1 coverage, split
into mocked/real/API tiers, parallelized where state allows, and reporting via
Playwright's native HTML report instead of TestRail. Cypress and its 9 npm
dependencies are removed in the same PR.

## 2. Current state (for reference)

| Item | Detail |
|---|---|
| Specs | `cypress/e2e/ps8/01`–`09`, ~45 `it()` blocks, PS1785 matrix entry commented out |
| Coverage shape | ~18 checkouts × Orders API, ~13 × Payments API, each followed by a BO ship/refund test |
| Isolation | None — tests read `:nth-child(1) > .column-payment` (newest order); methods are bulk-flipped between APIs mid-suite |
| Tunnel | Fixed ngrok subdomain (`demoshop8.ngrok.io`), baked into `docker-compose.8.yml` and into `tests/seed/database/prestashop_8.sql` (`ps_shop_url`, `PS_SHOP_DOMAIN[_SSL]`) |
| Reporting | `cypress-testrail`, every test name carries a `C######` TestRail case ID |
| Visual | `cy.matchImage()` at 0.01 threshold, fullPage, plus a mobile viewport spec |
| Known gaps | Two tests permanently `it.skip` (voucher/giftcard) — Mollie's test account auto-disables those methods; only the `paid` outcome is ever exercised, never `failed`/`canceled`/`expired` |
| Partial prior fix | `origin/BUGFIX/fix-e2e-tests-post-redesign` already maps some new-design selectors and adds a missing `make build-react` CI step — reference only, not a base to build on (still Cypress, still order-coupled) |

## 3. Scope decisions

| Decision | Chosen |
|---|---|
| Cypress/Playwright coexistence | Full replacement in one PR — delete `cypress/`, `cypress.config.js`, `cypress.env.json`, 9 devDependencies, `E2E_On_PR.yml`, in the same change |
| Coverage model | Risk-based by checkout **shape**, not 1:1 per method |
| Mocking | Tiered — see §5 |
| PS versions | PS8 + PS1785 (re-enabled) |
| Reporting | Drop TestRail. Playwright HTML + blob + GitHub reporters. `C######` IDs are **not** retained as tags — they're TestRail artifacts with no other consumer once TestRail is dropped |
| Locators | `data-testid` added to module React code only — no `htmlFor`/`aria-label` changes |
| Visual regression | Dropped. Replaced with structural assertions (sections/controls present, form saves) |
| Test agents (planner/generator/healer) | Used during this migration to explore the redesigned UI and author the initial suite. **Not committed** — deliverable is the tests, not agent scaffolding |
| Extra suites carried over | Subscriptions, Mobile viewport, Module install/uninstall. **Not** carried over: CloudSync/PS Accounts UI assertions (third-party iframe/shadow-DOM, known flake source, low value) |
| Tunnel | Cloudflare **named tunnels** on `invertusdemo.com`, pre-provisioned one per matrix job, tunnel token per job as a GitHub secret |
| Orders API vs Payments API phasing | Sequential phases within one job now; do not pre-emptively split into concurrent jobs. Revisit only if the merged HTML report's timings show the Payments phase is the actual bottleneck |

## 4. Why not 1:1 parity (coverage rationale)

The current suite tests ~31 methods × 2 APIs as if each combination were an
independent risk. It isn't — Mollie's checkout page renders one of a handful of
*shapes*, and the shape is what determines what can break:

| Shape | Example methods | What's actually being tested |
|---|---|---|
| `redirect` | Bancontact, PayPal, EPS, Przelewy24, Belfius | Straight handoff + outcome handling |
| `issuer-list` | iDEAL, KBC/CBC | Issuer selection step before handoff |
| `card-components` | Credit card | Mollie Components iframes on the FO, single-click flag |
| `authorize` | Klarna (Pay Later/Slice It/Pay Now), Billie | Authorize → BO ship → capture, not immediate `paid` |
| `async` | Bank transfer | No immediate confirmation |
| `multi-step` | Voucher, Gift card | Partial payment + fallback method (currently `fixme` — Mollie test account auto-disables these) |

One full UI checkout per shape, per API phase, covers the code path. The remaining
methods are covered by a single data-driven **"every enabled method renders at the FO
payment step"** test (cheap, catches availability/eligibility regressions — this is
where Twint, Blik, Bancomat, Bancontact-QR etc. get coverage without paying for a full
checkout each).

## 5. Three-tier API strategy

**Core principle** (per Playwright's own guidance): mock third parties you don't own;
never mock your own frontend-to-backend boundary. A mock of the module's own AJAX
tests a fiction — Mollie's actual bug history here (`PIPRES-781` VAT calculation,
`PIPRES-691` rounding, `PIPRES-702`/`PIPRES-707` negative price/surcharge) is entirely
in the "our payload didn't match what Mollie's API validates" class, which only a real
sandbox call can catch.

| Tier | What | Mollie traffic | Tunnel | Parallel |
|---|---|---|---|---|
| **A — Admin** | New React settings UI, subscriptions, install/uninstall, mobile viewport | Real, outbound-only (methods list, API key validation) | No | Full |
| **B — Checkout** | One checkout per shape × 2 API phases, **plus failure outcomes** (`failed`/`canceled`/`expired`, not just `paid`), BO ship/refund | Real sandbox, in + out | Yes | Within each phase |
| **C — Webhook guard paths** | `401` no client, `400` missing token, `422` missing id, unknown-transaction handling, **`409` concurrent-lock conflict** (`applyLock()` in `webhook.php`) | None | No | Full |

Explicitly out of scope: mocking `api.mollie.com` itself. The module builds its client
as `new MollieApiClient(new CurlPSMollieHttpAdapter())`
(`src/Service/ApiKeyService.php:34`) with no endpoint override and a hardcoded CA
bundle (`src/Adapter/API/CurlPSMollieHttpAdapter.php:97-98`) — mocking it would need a
container-level interception seam that doesn't exist today and isn't worth adding for
this migration.

**Known limitation:** the webhook controller re-fetches the transaction from Mollie by
ID rather than trusting the POST body (`controllers/front/webhook.php:126-140`), so
tier C cannot replay synthetic status-transition payloads for arbitrary methods. Tier C
is guard-path coverage only; per-method status-transition coverage lives in tier B.

**Third parties actually mocked/blocked** in tier A: Segment analytics, the CloudSync
iframe, PS Accounts shadow-DOM widget, `addons.prestashop.com` links — none of these
are module-owned behavior, and blocking them also removes the `cy.wait(15000)` /
`cy.wait(3000)` calls that exist purely to wait for them today.

## 6. Test isolation

Global shop config (API key, per-method enable/API/surcharge, `MOLLIE_IMAGES`,
single-click) is the one piece of state that breaks under naive parallelism. Approach,
in priority order — eliminate conflicts first, group only what's irreducible:

1. **Config written directly via SQL** in setup projects, not by clicking through 21
   methods × 8 fields per phase. One focused tier-A test still covers the *UI* of
   enabling a single method.
2. **Worker-scoped FO customers**, pre-seeded in the SQL dump, keyed on
   `parallelIndex` — carts and order histories never collide across workers.
3. **Orders looked up by reference**, never by grid position
   (`:nth-child(1) > .column-payment` as today).
4. **Idempotent settings saves** — the advanced-settings save test writes back the same
   values it read, so it can run in the parallel pool instead of the serial one.
5. What's left after 1–4 — `MOLLIE_IMAGES` big/hide, single-click on/off — is the only
   genuinely order-dependent, config-mutating work. It runs in one `workers: 1`
   project, not spread across the parallel pool.

### Project graph

```
bo-auth (setup)
 ├─ admin            [parallel]   — tier A + mobile + subscriptions + install/uninstall
 ├─ webhook          [parallel]   — tier C, no browser
 ├─ cfg-orders (setup) → checkout-orders     [parallel within phase]
 │                        └─ cfg-payments (setup) → checkout-payments [parallel within phase]
 └─ config-mutating  [workers: 1] — MOLLIE_IMAGES, single-click
```

`checkout-payments` depends on `cfg-payments`, which depends on `checkout-orders`
completing — this is the sequential-phases decision from §3, reused here as the actual
dependency chain.

## 7. Layout

```
tests/e2e/
  playwright.config.ts
  fixtures/            merged fixture: console-error guard (with documented
                        exceptions), third-party blocker, worker-scoped FO
                        customer, BO storageState
  setup/               bo-auth.setup.ts, cfg-orders.setup.ts, cfg-payments.setup.ts
  pages/
    admin/              Authorization, PaymentMethods, AdvancedSettings,
                         Subscriptions, AdminOrder (refund/ship panel)
    front/               Checkout, OrderHistory, OrderConfirmation
    mollie/              HostedCheckout (the sandbox outcome-picker page)
  specs/
    admin/               tier A + subscriptions + mobile + install-uninstall
    checkout/            tier B
    webhook/             tier C
  data/                payment-methods.ts — the shape/API/country registry (§4)
  helpers/             config.ts (SQL config writer), orders.ts (lookup by reference)
```

`tests/e2e/**` does not collide with the existing PHPUnit `tests/` (PSR-4
`Mollie\Tests\` in `composer.json`); `make prepare-zip` already deletes `tests`
wholesale for the packaged zip and will delete this too.

## 8. CI

Two jobs per PS version (PS8, PS1785 — 4 jobs total):

- **`local`** — shop built with `PS_DOMAIN=localhost:8002` (both `docker-compose.8.yml`
  and `docker-compose.1785.yml` already map port 8002 externally; each PS version runs
  on its own matrix runner, so the shared port number is not a conflict), no tunnel.
  Runs `admin` + `webhook` projects. 3–4 workers (capped by the single PHP-FPM/MySQL
  stack per runner, not the runner's core count — start conservative, measure before
  raising).
- **`checkout`** — Cloudflare named tunnel
  (`ps8-checkout.invertusdemo.com` / `ps1785-checkout.invertusdemo.com`), tunnel token
  per job as a GitHub secret. Runs `cfg-orders → checkout-orders → cfg-payments →
  checkout-payments` sequentially. 1–2 workers within each phase.

Reporters: `[html, blob, github]`. Blob reports merged across jobs into one HTML
report, uploaded as a CI artifact. `trace: 'on-first-retry'`,
`video: 'retain-on-failure'`. Retries: 2 locally, 1 in CI for the `checkout` job only
(tier A/C are deterministic enough not to need it; masking real bugs with retries on
those tiers is a non-goal).

**Fixed as part of this migration** (unrelated to Playwright itself, but blocking real
parallelism/speed):
- `sleep 90s` → real health-check poll against the shop.
- `npm ci` currently runs twice, plus a redundant `npm install -D cypress` and
  `npx browserslist@latest --update-db` — all removed.
- The SQL seed's baked-in `demoshop8.ngrok.io` (`ps_shop_url`,
  `PS_SHOP_DOMAIN[_SSL]`) is overwritten post-seed by a `make set-shop-domain
  HOST=...` step, needed for both the `local` job (`localhost:8002`) and the
  `checkout` job (the named tunnel hostname) — one mechanism serves both.
- `NGROK_TOKEN` secret removed; replaced by per-job Cloudflare tunnel token secrets.

## 9. Module code changes

`data-testid` attributes added to the React components under
`views/js/admin/library/src/pages/{authorization,payment-methods,advanced-settings}`
that tests need to target reliably (API key input, connect button, mode toggle, method
enable switches, save button) — roughly 20–30 attributes. No accessibility markup
changes. Locator priority otherwise still follows standard practice: `getByRole` /
`getByText` where the existing DOM supports it, `getByTestId` only where it doesn't.

## 10. Explicitly out of scope

- Container-level `api.mollie.com` interception (WireMock/Prism, CA injection, or a
  production `setApiEndpoint()` test seam).
- CloudSync / PS Accounts iframe and shadow-DOM assertions.
- TestRail integration and `C######` case-ID tracking.
- Visual/screenshot regression.
- Splitting Orders-API/Payments-API checkout phases into concurrent jobs (revisit only
  if measured timings justify the extra environment build).

## 11. Risks carried into planning

1. **PS1785 was disabled with the comment "possible bug blocker."** Re-enabling it may
   surface a real product bug rather than a test problem — to be diagnosed, not
   papered over, during implementation.
2. **PS1785 runs the same redesigned React roots** (the prior fix branch touched
   `ps1785` specs with the same `#mollie-*-root` selectors) — page objects should be
   shared between PS8 and PS1785, but this needs confirming against a running PS1785
   shop, not assumed.
3. **Cloudflare quick-tunnel-class services carry no uptime SLA** even as named
   tunnels — add startup retry logic rather than assume first-try success.
4. **Worker count is capped by the shop's own resources**, not the runner's. Start at
   3–4 for tiers A/C and measure.
5. **Parallel checkouts may surface real concurrency bugs**, given
   `PIPRES-720/race-condition-prevention` and the existing `Lock` adapter. Valuable
   signal, but budget time during implementation to separate real races from test
   flakiness.
6. **Voucher and Gift card stay `test.fixme()`**, not silently dropped — Mollie's test
   account auto-disables these methods; the current suite already carries this as a
   permanent `it.skip`.

## 12. Out-of-band items noticed, not part of this migration

- `controllers/front/webhook.php:71` validates only that `security_token` is
  *present*, never that it's valid, before using it as the lock key. May be
  intentional; flagged for separate review, not changed here.
