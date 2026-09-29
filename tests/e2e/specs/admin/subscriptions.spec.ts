import { test, expect } from '../../fixtures/base';
import { SubscriptionsPage } from '../../pages/admin/subscriptions-page';

test('subscriptions grid renders', async ({ page }) => {
  const subs = new SubscriptionsPage(page);
  await subs.gotoOrders();
  await expect(page.locator('#invertus_mollie_subscription_grid_panel')).toBeVisible();
});

test('subscriptions FAQ tab renders', async ({ page }) => {
  const subs = new SubscriptionsPage(page);
  await subs.gotoFAQ();
  await expect(page.getByText('Subscription creation').first()).toBeVisible();
  await expect(page.getByText('Cart rules').first()).toBeVisible();
});
