/**
 * The single source of truth for which methods are exercised, on which API, and
 * in which checkout shape. Consumed by `cfg-orders.setup.ts`,
 * `cfg-payments.setup.ts` and `checkout.spec.ts`.
 *
 * Coverage is risk-based: one method per checkout shape rather than every
 * method Mollie offers.
 */

export type CheckoutShape =
  | 'redirect'
  | 'issuer-list'
  | 'card-components'
  | 'authorize'
  | 'async'
  | 'multi-step';

export type MethodDef = {
  id: string;
  label: RegExp;
  /**
   * Excludes options whose label also matches `label` — needed since Mollie
   * renamed iDEAL to "iDEAL | Wero", which any /wero/i matcher also hits.
   */
  notLabel?: RegExp;
  apis: ReadonlyArray<'orders' | 'payments'>;
  shape: CheckoutShape;
  billingCountry: 'NL' | 'DE' | 'UK' | 'PL' | 'CH';
  /**
   * Mollie's own order-value window for the method, in EUR, as reported by
   * `GET /v2/methods?amount[value]=…`. A cart outside it is simply not offered
   * the method, so the checkout specs size the cart from `minAmount` and the
   * availability spec asserts the method disappears below it.
   */
  minAmount?: number;
  maxAmount?: number;
  /** Set when the method cannot be exercised; the test is skipped, not deleted. */
  fixme?: string;
};

export const paymentMethods: MethodDef[] = [
  { id: 'bancontact', label: /bancontact/i, apis: ['orders'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'ideal', label: /ideal/i, apis: ['payments'], shape: 'issuer-list', billingCountry: 'NL' },
  // Mollie Components is on in sandbox (MOLLIE_SANDBOX_IFRAME=1), so the card
  // fields render inline in four Mollie iframes on the shop's own payment
  // step. checkout.spec.ts types a test card into them (MollieComponentsForm)
  // before placing the order; the module then tokenises client-side and
  // resubmits (views/js/front/mollie_iframe.js).
  {
    id: 'creditcard',
    label: /card/i,
    notLabel: /saved|gift/i,
    apis: ['payments'],
    shape: 'card-components',
    billingCountry: 'NL',
  },
  // `klarnapaylater` (with `klarnapaynow`/`klarnasliceit`) is the legacy split
  // Mollie has since consolidated into one `klarna` method — the test profile
  // returns `klarna` and never `klarnapaylater`, so the old ID matched nothing
  // and both its tests skipped themselves as "not offered" on every run.
  { id: 'klarna', label: /klarna/i, apis: ['orders'], shape: 'authorize', billingCountry: 'NL' },
  { id: 'billie', label: /billie/i, apis: ['orders'], shape: 'authorize', billingCountry: 'DE' },
  { id: 'banktransfer', label: /bank transfer/i, apis: ['orders'], shape: 'async', billingCountry: 'NL' },
  // Verified against the test profile: in3 is offered at EUR 50 and EUR 250 but
  // not at EUR 20 or EUR 6000. The 5000 previously recorded as `minAmount` is
  // the method's maximum, which sized every in3 cart far ABOVE the window and
  // left both in3 checkout tests permanently skipped as "not offered".
  {
    id: 'in3',
    label: /in 3/i,
    apis: ['orders'],
    shape: 'redirect',
    billingCountry: 'NL',
    minAmount: 50,
    maxAmount: 5000,
  },
  { id: 'paypal', label: /paypal/i, apis: ['payments'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'eps', label: /eps/i, apis: ['payments'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'kbc', label: /kbc\/cbc/i, apis: ['payments'], shape: 'issuer-list', billingCountry: 'NL' },
  {
    id: 'voucher',
    label: /voucher/i,
    apis: ['orders'],
    shape: 'multi-step',
    billingCountry: 'NL',
    fixme: "Mollie's test account auto-disables this method",
  },
  {
    id: 'giftcard',
    label: /gift ?card/i,
    apis: ['orders'],
    shape: 'multi-step',
    billingCountry: 'NL',
    fixme: "Mollie's test account auto-disables this method",
  },

  // --- Coverage sweep (PIPRES-804): every remaining method the test profile
  // offers in EUR for a seeded billing country (NL/DE/UK/PL/CH), verified via
  // GET /v2/methods?billingCountry=… against the CI profile. Not listed
  // because not testable: alma + bizum (never offered for these countries),
  // twint + blik (CHF/PLN only — the shop sells in EUR), applepay + googlepay
  // (need a wallet-capable browser), swish (SEK), directdebit (recurring
  // only). API assignment alternates to keep the two phase invocations
  // balanced; all of these are plain hosted-page redirects unless noted.
  { id: 'przelewy24', label: /przelewy24/i, apis: ['payments'], shape: 'redirect', billingCountry: 'PL' },
  { id: 'belfius', label: /belfius/i, apis: ['payments'], shape: 'redirect', billingCountry: 'NL' },
  // "iDEAL | Wero" also matches /wero/i, hence the notLabel.
  { id: 'wero', label: /wero/i, notLabel: /ideal/i, apis: ['orders'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'bancomatpay', label: /bancomat pay/i, apis: ['orders'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'paybybank', label: /pay by bank/i, apis: ['payments'], shape: 'redirect', billingCountry: 'NL' },
  // BNPL like klarna/billie: authorized at checkout, captured on shipment.
  { id: 'riverty', label: /riverty/i, apis: ['orders'], shape: 'authorize', billingCountry: 'NL', minAmount: 5 },
  { id: 'satispay', label: /satispay/i, apis: ['payments'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'mbway', label: /mb way/i, apis: ['payments'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'multibanco', label: /multibanco/i, apis: ['orders'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'vipps', label: /vipps/i, apis: ['orders'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'mobilepay', label: /mobilepay/i, apis: ['payments'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'billink', label: /billink/i, apis: ['orders'], shape: 'redirect', billingCountry: 'NL' },
];
