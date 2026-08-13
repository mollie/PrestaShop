import { test, expect } from '../../fixtures/base';
import { AdminProductPage } from '../../pages/admin/product-page';
import { FrontProductPage } from '../../pages/front/product-page';
import { MyAccountPage } from '../../pages/front/my-account-page';
import {
  SUBSCRIPTIONS_ENABLED_KEY,
  getGlobalConfig,
  restoreGlobalConfig,
  setGlobalConfig,
} from '../../helpers/config';
import {
  productType,
  subscriptionAttributeIds,
  subscriptionAttributeNames,
  subscriptionCombinationCount,
} from '../../helpers/subscription';

/**
 * The three Cypress subscription cases the Playwright suite had no equivalent
 * for — C176305 (subscription options in the product BO), C1672516 (the
 * subscription dropdown on the FO product page) and C1672517 (the Subscriptions
 * tab in My Account). The existing `admin/subscriptions.spec.ts` only covers the
 * BO grid and the FAQ tab.
 *
 * What is actually under test is the module's install-time attribute group
 * (`subscription/Install/AttributeInstaller.php`): it has to reach the
 * merchant's combination generator, survive into a product's combinations, and
 * surface to the customer as a "Subscription" dropdown.
 *
 * Serial and config-mutating: it flips `MOLLIE_SUBSCRIPTION_ENABLED` and turns a
 * product into a combinations product, and the FO assertions read what the BO
 * test wrote.
 */
test.describe.configure({ mode: 'serial' });

/**
 * A product no other spec touches. `checkout-page.ts` pins #1 (default) and #8
 * (cheapest), and switching a product to combinations resets its stock — doing
 * that to either would quietly change what the checkout specs are buying.
 * #7 is a plain standard product of the same kind as #8.
 */
const PRODUCT_ID = 7;

let previousSubscriptionsEnabled: string | null = null;

test.beforeAll(() => {
  previousSubscriptionsEnabled = getGlobalConfig(SUBSCRIPTIONS_ENABLED_KEY);
  // The FO account tab is gated on this; the product-side options are not.
  setGlobalConfig(SUBSCRIPTIONS_ENABLED_KEY, '1');
});

test.afterAll(() => {
  restoreGlobalConfig(SUBSCRIPTIONS_ENABLED_KEY, previousSubscriptionsEnabled);
});

test('the module offers its subscription attributes in the product BO', async ({ page }) => {
  // Two saves of the product plus a combination generation.
  test.setTimeout(240_000);

  const ids = subscriptionAttributeIds();
  expect(
    ids.group,
    'no SUBSCRIPTION_ATTRIBUTE_GROUP in configuration — the module did not install its attribute group'
  ).not.toBeNull();
  for (const [name, id] of Object.entries(ids)) {
    expect(id, `no attribute id stored for "${name}"`).not.toBeNull();
  }

  const product = new AdminProductPage(page);
  await product.goto(PRODUCT_ID);

  // Idempotent: a re-run against an already-converted product must assert, not
  // convert again (the second conversion would wipe the combinations).
  if (productType(PRODUCT_ID) !== 'combinations') {
    await product.switchToCombinationsType();
  }
  expect(productType(PRODUCT_ID), 'the product did not become a combinations product').toBe('combinations');

  await product.openCombinationsTab();
  const generator = await product.openCombinationsGenerator();

  // C176305's real subject: the module's group is offered to the merchant
  // alongside the shop's own (Size, Color, …).
  const groups = (await product.generatorAttributeGroups(generator)).map((g) => g.trim());
  expect(groups, `the generator offers no "Mollie Subscription" group, only: ${groups.join(', ')}`).toContain(
    'Mollie Subscription'
  );

  await product.expandAttributeGroup(generator, ids.group!);
  // Every frequency the module installs must be selectable, not just the two
  // this spec goes on to generate.
  for (const name of subscriptionAttributeNames()) {
    await expect(
      generator.locator(`#attribute-group-${ids.group}`).getByText(name, { exact: true }),
      `the generator does not offer the "${name}" subscription option`
    ).toHaveCount(1);
  }

  const alreadyGenerated = subscriptionCombinationCount(PRODUCT_ID, ids.group!);
  if (alreadyGenerated === 0) {
    await product.selectAttributes(generator, [ids.daily!, ids.none!]);
    await product.confirmGeneration(generator);
  } else {
    // Already prepared by an earlier run: close the generator instead of
    // generating a second, duplicate set.
    await generator.locator('button.close').first().click().catch(() => {});
  }

  // The generator writes the combinations immediately; the rows are how the
  // merchant sees that, and the count is how the shop stored it.
  await expect(
    product.combinationRows(/Mollie Subscription - Daily/i),
    'no "Mollie Subscription - Daily" combination row after generating'
  ).toHaveCount(1);
  await expect(
    product.combinationRows(/Mollie Subscription - None/i),
    'no "Mollie Subscription - None" combination row after generating'
  ).toHaveCount(1);
  expect(
    subscriptionCombinationCount(PRODUCT_ID, ids.group!),
    'the generated combinations do not carry the subscription attributes'
  ).toBeGreaterThanOrEqual(2);
});

test('the subscription dropdown is offered on the FO product page', async ({ page }) => {
  const ids = subscriptionAttributeIds();
  test.skip(
    subscriptionCombinationCount(PRODUCT_ID, ids.group!) === 0,
    'the product carries no subscription combinations — see the product BO test above'
  );

  const product = new FrontProductPage(page);
  await product.goto(PRODUCT_ID);

  // The classic theme renders an attribute group's public name as the select's
  // aria-label, and the module names its group "Subscription" for the customer.
  await expect(
    product.subscriptionSelect(),
    'the product page renders no Subscription dropdown'
  ).toBeVisible();

  const options = (await product.subscriptionOptions()).map((o) => o.trim());
  expect(options, `the Subscription dropdown offers: ${options.join(', ')}`).toContain('Daily');
  expect(options, `the Subscription dropdown offers: ${options.join(', ')}`).toContain('None');
});

test('My Account offers the Subscriptions tab and it opens the module page', async ({ page }) => {
  const account = new MyAccountPage(page);
  await account.goto();

  await expect(
    account.subscriptionsLink(),
    'the account page offers no Subscriptions tab while subscriptions are enabled'
  ).toHaveCount(1);

  await account.openSubscriptions();
  expect(page.url(), 'the Subscriptions tab does not open the module controller').toContain('module/mollie/subscriptions');
  await expect(page.locator('#content, .page-content').first()).toBeVisible();
});

test('the Subscriptions tab is not offered when subscriptions are disabled', async ({ page }) => {
  // The other half of SubscriptionAvailabilityProvider: with the feature off and
  // no subscription orders of their own, a customer must not see the tab.
  setGlobalConfig(SUBSCRIPTIONS_ENABLED_KEY, '0');
  try {
    const account = new MyAccountPage(page);
    await account.goto();
    await expect(
      account.subscriptionsLink(),
      'the Subscriptions tab is offered even though subscriptions are disabled'
    ).toHaveCount(0);
  } finally {
    setGlobalConfig(SUBSCRIPTIONS_ENABLED_KEY, '1');
  }
});
