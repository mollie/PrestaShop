# Closing the Cypress → Playwright coverage gap

Date: 2026-08-07
Branch: `PIPRES-804/e2e-test-rework`

Scope: the three highest-value gaps identified when comparing the removed Cypress
suite (`95c06005a^:cypress/`) against `tests/e2e`. Breadth gaps (Przelewy24,
Belfius, Bancomat, Klarna UK, per-API duplication) and visual regression are
explicitly **out of scope** — see "Deliberately not doing" at the end.

---

## 1. Credit card

### What Cypress had

`CreditCardFillingIframe` typed a test card into four Mollie Components iframes,
then checkout ran on both APIs (C339354, C339383). C339355 asserted the
single-click path by re-entering checkout and submitting without re-typing a
card. C339356/C339384 refunded the resulting order.

### Why the Playwright test is `fixme`

`data/payment-methods.ts` marks `creditcard` `fixme` because `MOLLIE_SANDBOX_IFRAME`
is on in the seed, so the card fields render inline in four Mollie iframes on the
shop's own payment step rather than on Mollie's hosted page. `placeOrder()` then
never leaves `/order`, and `HostedCheckoutPage.chooseOutcome` has nothing to click.

### Plan

`MOLLIE_SANDBOX_IFRAME` (`Config::MOLLIE_IFRAME['sandbox']`) is a plain
`ps_configuration` row, so `helpers/config.ts::setGlobalConfig` can flip it. That
splits the work into two independent tests instead of one blocked one.

**1a — card via the hosted page (`MOLLIE_SANDBOX_IFRAME=0`).**
New spec `specs/checkout/card-hosted.spec.ts`, own Playwright project
`checkout-card` (depends on a new `cfg-card` setup that writes the config row and
assigns `creditcard` to the phase's API via `setMethodConfig`). Flow is exactly
the existing one — `CheckoutPage.start` → `selectMethod(/card/i)` → `placeOrder`
→ `HostedCheckoutPage.chooseOutcome('paid')` → `expectConfirmation` → BO refund.
Reuses every existing page object; no new interaction code. Gated by
`requiresPublicHost()` like the rest of the checkout phase.

Remove the `fixme` from the registry entry and drop `creditcard` from
`payment-methods.ts`'s generic loop only if the dedicated project supersedes it;
otherwise leave it registry-driven and let `cfg-card` own the config.

**1b — card via Mollie Components (`MOLLIE_SANDBOX_IFRAME=1`).**
New page object `pages/front/card-components.ts`. The four mount points are
`#card-holder-creditcard`, `#card-number-creditcard`, `#expiry-date-creditcard`,
`#verification-code-creditcard` (`views/templates/hook/mollie_iframe.tpl`); Mollie's
script mounts one cross-origin iframe into each. Fill with
`page.frameLocator('#card-number-creditcard iframe').locator('input')` and friends.

Submitting is not a plain click: `views/js/front/mollie_iframe.js` intercepts the
form `submit`, calls `mollie.createToken()`, writes the token into
`input[name="mollieCardToken"]` and re-submits. So the test must click confirm and
then wait for either a navigation off `/order` **or** `.js-mollie-alert` becoming
visible — asserting the alert stays empty is the real regression guard for
tokenisation.

Test card: Mollie's documented test PAN, any future expiry, any CVC. Put it in
`data/test-cards.ts` alongside a non-3DS variant so C339386's intent survives even
though Cypress had it skipped.

**1c — single click / customerId (C339355).**
Depends on 1b. With `MOLLIE_SANDBOX_SINGLE_CLICK_PAYMENT=1` (also a
`ps_configuration` row, `Config::MOLLIE_SINGLE_CLICK_PAYMENT['sandbox']`) and
`#mollie-save-card` ticked on the first payment, the second checkout must render
`#mollie-use-saved-card` and complete **without** any iframe input. Cypress
asserted nothing here; assert both:
  - the saved-card checkbox is present and checked, and
  - a `ps_mol_customer` row exists for the worker's email
    (`SELECT customer_id FROM ps_mol_customer WHERE email = ?` via `helpers/db.ts`)
    — that column is the Mollie customer id the whole feature exists to reuse.

Single-click must run serially against one worker customer (`test.describe.serial`)
because both tests share that customer's saved card.

---

## 2. Partial refunds (Payments API)

### What Cypress had

`OrderRefundingPartialPaymentsAPI` typed an amount into the refund field, confirmed
the SweetAlert, and asserted `Refund was made successfully!` (C339380, C339382,
C339384, C339388, C339392, C339396, C339398, C339400).

### What exists now

`AdminOrderPage.refund()` only drives the per-line `.mollie-refund-btn` → confirm
path and tolerates a disabled control. The amount-based control is a different
widget entirely, rendered only under `{if $mollie_api_type == 'payments'}` in
`views/templates/hook/order_info.tpl`:

- `#mollie-refund-amount` — number input, pre-filled with `$refundable_amount`,
  `disabled` when `$isRefunded || $refundable_amount <= 0`
- `#mollie-initiate-refund` — button; `order_info.js` validates `amount > 0`, then
  opens `#mollieRefundModal`
- `#mollieRefundModalConfirm` — fires the AJAX; on success `showSuccessMessage`
  prepends `.alert-success` inside `.mollie-order-info-panel`, then reloads after 1.5s

### Plan

Add to `AdminOrderPage`:

```
refundableAmount(): Promise<number>          // reads #mollie-refund-amount value
partialRefund(amount: number): Promise<boolean>  // fill, initiate, confirm
expectRefundSucceeded(): Promise<void>       // .mollie-order-info-panel .alert-success
```

`partialRefund` returns false when the input or button is disabled, matching how
`refund()` already reports "Mollie does not currently allow it" rather than failing.

Assertion after a partial refund of half the refundable amount: the success alert
appears **and**, after the auto-reload, `#mollie-refund-amount` has dropped by the
refunded amount. That second assertion is what actually proves the refund landed —
the alert alone only proves the AJAX returned `success`.

Wire it into `checkout.spec.ts`: on the `payments` phase, after the existing full
refund is replaced by a half refund, then a second refund of the remainder. Keep
the Orders-API branch untouched (no amount control is rendered there).

Also worth adding while in this file, since the controls sit side by side and
Cypress never covered them: `#mollie-capture-amount` / `#mollie-initiate-capture`
for an authorize-shape method on the Payments API. Flagged, not committed to.

---

## 3. Subscriptions functional trio

### What Cypress had

- **C176305** — BO: change product #8 to a combinations product, generate
  combinations from the Mollie attribute group, set quantities, save.
- **C1672516** — FO product page shows the `[aria-label="Subscription"]` dropdown.
- **C1672517** — FO My Account → Subscriptions page renders.

### Constraint

C176305 as written drives PrestaShop's product page (`.product-type-preview`,
`#combination_list`, `Generate combinations`). That UI is completely different
between PS8 and PS1785, and the whole point of the current suite is one spec set
across both seeds. Porting it selector-for-selector reintroduces the per-version
fork the migration removed.

### Plan

Split it by what is actually being tested.

**3a — the module's attribute group is installed and offered (replaces C176305's
premise).**
`subscription/Install/AttributeInstaller.php` creates an `AttributeGroup` named
`Mollie Subscription` (public name `Subscription`, `group_type = 'select'`) with one
attribute per `Config::getSubscriptionAttributeOptions()` — None, Daily, Weekly,
Monthly, Quarterly, Yearly — recording each id in `ps_configuration` under
`SUBSCRIPTION_ATTRIBUTE_*`.

New spec `specs/admin/subscription-attributes.spec.ts`:
- assert via `helpers/db.ts` that `SUBSCRIPTION_ATTRIBUTE_GROUP` resolves to a live
  `ps_attribute_group` row whose lang name is `Mollie Subscription`
- assert all six `SUBSCRIPTION_ATTRIBUTE_*` ids resolve to `ps_attribute` rows in
  that group
- assert in the BO Attributes screen (`AdminAttributesGroups`, same controller on
  both versions) that the group is listed

This is version-agnostic and tests the module's own code, which the Cypress
combination-generator walk mostly did not.

**3b — FO product page renders the Subscription dropdown (C1672516).**
Needs a product that actually has subscription combinations. Rather than driving
the BO generator, seed them: new `helpers/subscriptions.ts` with
`ensureSubscriptionCombinations(productId)` that inserts `ps_product_attribute` +
`ps_product_attribute_combination` + `ps_product_attribute_shop` rows joining the
product to the `None` and `Daily` attribute ids read from `ps_configuration`, and
flips the product to `cache_default_attribute`. Idempotent, same shape as
`setMethodConfig`.

Then the spec is one navigation and one assertion:
`[aria-label="Subscription"]`, or the group's `<select>` in the theme's
`.product-variants` block — resolve the exact hook once against a running shop,
since PS1785 renders `aria-label` differently.

Run it in the `admin` project? No — it is a front-office assertion with a DB
precondition; give it its own `subscriptions` project depending on `bo-auth` so it
does not race the checkout phases over the same product. Use a product **not** used
by `CheckoutPage` (not #1, not #8) to keep the carts clean.

**3c — FO My Account → Subscriptions (C1672517).**
`controllers/front/subscriptions.php` is `mollieSubscriptionsModuleFrontController`.
Assert the My Account page links to it and that the page renders its content block
for a signed-in worker customer. Cheapest of the three; no DB setup.

---

## Order of work

1. **2 (partial refunds)** — smallest, no new project, unblocks a real assertion gap
   in a phase that already runs green.
2. **3c → 3a → 3b (subscriptions)** — increasing setup cost; 3b is the only one
   needing new SQL helpers.
3. **1a → 1b → 1c (card)** — 1a is nearly free and gets card checkout running at
   all; 1b/1c are the genuinely new interaction code.

Each step lands as its own commit with the spec passing locally against the PS8
seed, then re-run against PS1785 before the next one starts.

---

## Deliberately not doing

- **Method breadth** (Przelewy24, Belfius, Bancomat, Klarna UK, per-API
  duplication). The registry is risk-based by checkout shape on purpose; adding
  methods multiplies tunnel time without covering new module code. If parity is
  wanted as a hard requirement, that is a separate decision, not a side effect of
  this work.
- **Visual regression** (`cy.matchImage`). Playwright's `toHaveScreenshot` would
  need baselines per PS version, per viewport, in CI — a project of its own, and
  the console-error guard in `fixtures/base.ts` already catches the class of
  breakage the snapshots were mostly catching in practice.
- **CloudSync / PS Accounts UI** (C2885757, C2885758). Those hosts are in
  `THIRD_PARTY_BLOCKLIST` by design; un-blocking them to assert their UI would make
  every other test depend on a third-party service being up.
- **C339338 carriers in Payment Preferences**, **C339360 in3 logo via
  `MOLLIE_IMAGES`**, **guest checkout**. Low value or already skipped in Cypress;
  logged here so the omission is a choice rather than an oversight.
