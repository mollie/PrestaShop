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
  apis: ReadonlyArray<'orders' | 'payments'>;
  shape: CheckoutShape;
  billingCountry: 'NL' | 'DE' | 'UK';
  minAmount?: number;
  /** Set when the method cannot be exercised; the test is skipped, not deleted. */
  fixme?: string;
};

export const paymentMethods: MethodDef[] = [
  { id: 'bancontact', label: /bancontact/i, apis: ['orders'], shape: 'redirect', billingCountry: 'NL' },
  { id: 'ideal', label: /ideal/i, apis: ['payments'], shape: 'issuer-list', billingCountry: 'NL' },
  {
    id: 'creditcard',
    label: /card/i,
    apis: ['payments'],
    shape: 'card-components',
    billingCountry: 'NL',
    fixme:
      'Mollie Components is on in sandbox (MOLLIE_SANDBOX_IFRAME=1), so the card ' +
      'fields render inline in four Mollie iframes on the shop\'s own payment step ' +
      'rather than on a hosted page. Submitting with them empty is blocked ' +
      'client-side, so this needs a test card typed into those iframes — a ' +
      'different flow from every other method here.',
  },
  { id: 'klarnapaylater', label: /pay later/i, apis: ['orders'], shape: 'authorize', billingCountry: 'NL' },
  { id: 'billie', label: /billie/i, apis: ['orders'], shape: 'authorize', billingCountry: 'DE' },
  { id: 'banktransfer', label: /bank transfer/i, apis: ['orders'], shape: 'async', billingCountry: 'NL' },
  { id: 'in3', label: /in 3/i, apis: ['orders'], shape: 'redirect', billingCountry: 'NL', minAmount: 5000 },
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
];
