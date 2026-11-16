import { test, expect } from '../../fixtures/base';
import type { Locator } from '@playwright/test';
import {
  callAdvancedSettingsAjax as callAjax,
  findStatusMapping,
  openAdvancedSettingsForm as openForm,
} from '../../helpers/advanced-settings';

/**
 * The status sections are collapsed by default and reopen when a save fails on
 * one of their fields, so the save endpoint must name the rejected config keys
 * in `fieldErrors` and must not write the rejected value.
 */
test('save rejects an unknown order status and reports the field', async ({ page }) => {
  await openForm(page);

  const readOpenStatus = async () => (await findStatusMapping(page, 'MOLLIE_STATUS_OPEN'))?.prestashopStatus;

  const before = await readOpenStatus();
  expect(before).toBeDefined();

  const result = await callAjax(page, 'saveSettings', {
    statusMappings: [{ configKey: 'MOLLIE_STATUS_OPEN', prestashopStatus: '999999', mollieStatus: 'Open' }],
  });

  expect(result.success).toBe(false);
  expect(result.fieldErrors).toEqual(['MOLLIE_STATUS_OPEN']);
  expect(result.message).toBeTruthy();

  expect(await readOpenStatus()).toBe(before);
});

const firstNumber = async (locator: Locator) => Number((await locator.innerText()).match(/\d+/)?.[0]);

test('both status sections start collapsed with a summary', async ({ page }) => {
  const settings = await openForm(page);

  await expect(settings.statusMappingHeader()).toHaveAttribute('aria-expanded', 'false');
  await expect(settings.emailStatusesHeader()).toHaveAttribute('aria-expanded', 'false');
  await expect(page.locator('.status-mapping-row')).toHaveCount(0);
  await expect(page.locator('.email-status-row')).toHaveCount(0);
  await expect(settings.statusMappingHeader()).toHaveText(/\d+ of \d+ statuses mapped/);
  await expect(settings.emailStatusesHeader()).toHaveText(/\d+ of \d+ emails on/);
  await expect(page.getByTestId('advanced-settings-save')).toBeVisible();
});

test('one click opens a section and a second click closes it', async ({ page }) => {
  const settings = await openForm(page);

  await settings.statusMappingHeader().click();
  await expect(settings.statusMappingHeader()).toHaveAttribute('aria-expanded', 'true');
  expect(await page.locator('.status-mapping-row').count()).toBeGreaterThan(0);
  await expect(settings.emailStatusesHeader()).toHaveAttribute('aria-expanded', 'false');

  await settings.statusMappingHeader().click();
  await expect(settings.statusMappingHeader()).toHaveAttribute('aria-expanded', 'false');
  await expect(page.locator('.status-mapping-row')).toHaveCount(0);
});

test('an unsaved change survives closing and reopening', async ({ page }) => {
  const settings = await openForm(page);
  const header = settings.emailStatusesHeader();
  const firstToggle = () => page.locator('.email-status-row input[type=checkbox]').first();

  const countBefore = await firstNumber(header);
  await header.click();
  const wasChecked = await firstToggle().isChecked();

  await page.locator('.email-status-row .toggle-switch').first().click();
  await expect(firstToggle()).toBeChecked({ checked: !wasChecked });

  await header.click();
  await expect(header).toHaveAttribute('aria-expanded', 'false');
  await header.click();
  await expect(firstToggle()).toBeChecked({ checked: !wasChecked });

  const countAfter = await firstNumber(header);
  expect(countAfter).toBe(wasChecked ? countBefore - 1 : countBefore + 1);
});

test('a save that fails on a status mapping field reopens that section', async ({ page }) => {
  const settings = await openForm(page);

  await page.route('**/*', async (route) => {
    const request = route.request();
    if (request.method() !== 'POST' || !request.postData()?.includes('saveSettings')) {
      return route.continue();
    }

    return route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: false, message: 'mocked', fieldErrors: ['MOLLIE_STATUS_OPEN'] }),
    });
  });

  await expect(settings.statusMappingHeader()).toHaveAttribute('aria-expanded', 'false');
  await settings.save();

  await expect(settings.statusMappingHeader()).toHaveAttribute('aria-expanded', 'true');
  await expect(settings.emailStatusesHeader()).toHaveAttribute('aria-expanded', 'false');
  await expect(page.getByTestId('advanced-settings-notification')).toBeVisible();
});
