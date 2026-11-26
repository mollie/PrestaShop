import { test, expect } from '../../fixtures/base';
import { SubscriptionsPage } from '../../pages/admin/subscriptions-page';
import { dismissInvalidTokenWall } from '../../helpers/orders';

test('subscriptions grid renders', async ({ page }) => {
  const subs = new SubscriptionsPage(page);
  await subs.gotoOrders();
  await expect(page.locator('#invertus_mollie_subscription_grid_panel')).toBeVisible();
});

test('subscriptions FAQ card is collapsed and expands', async ({ page }) => {
  const subs = new SubscriptionsPage(page);
  await subs.gotoOrders();
  await expect(page.locator('#mollie-subscription-faq')).toBeVisible();
  await expect(page.locator('#mollie-subscription-faq-body')).toBeHidden();
  await page.locator('#mollie-subscription-faq .card-header').click();
  await expect(page.getByText('Subscription creation').first()).toBeVisible();
  await expect(page.getByText('Cart rules').first()).toBeVisible();
  await expect(
    page.getByText('Make sure to select the default carrier for recurring orders in the Subscription options at the top of this page.')
  ).toBeVisible();
  await expect(page.locator('body')).not.toContainText('in advanced settings');
});

test('old subscriptions FAQ link opens the subscriptions page', async ({ page }) => {
  await page.goto('/admin1/index.php?controller=AdminMollieSubscriptionFAQ');
  await dismissInvalidTokenWall(page);
  await expect(page.locator('#invertus_mollie_subscription_grid_panel')).toBeVisible({ timeout: 20_000 });
  expect(page.url()).toContain('admin-subscription');
  expect(page.url()).not.toContain('admin-subscription-faq');
});
