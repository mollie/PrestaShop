import { test as setup, expect } from '@playwright/test';
import { envValueOr } from '../helpers/env';

const email = envValueOr('E2E_BO_EMAIL', 'demo@prestashop.com');
const password = envValueOr('E2E_BO_PASSWORD', 'prestashop_demo');
const authFile = '.auth/bo.json';

setup('authenticate as BO admin', async ({ page }) => {
  await page.goto('/admin1/');
  await page.locator('#email').fill(email);
  await page.locator('#passwd').fill(password);
  await page.locator('#submit_login').click();
  await expect(page.locator('#header_logo, #header_infos').first()).toBeVisible({ timeout: 15_000 });
  await page.context().storageState({ path: authFile });
});
