import { test, expect } from '../../fixtures/base';
import type { Locator, Page } from '@playwright/test';
import { AdvancedSettingsPage } from '../../pages/admin/advanced-settings-page';
import { skipIfDisconnected } from '../../helpers/module-state';

type StatusMapping = { configKey: string; prestashopStatus: string; mollieStatus: string };

const callAjax = (page: Page, action: string, data?: Record<string, unknown>) =>
  page.evaluate(
    async ([action, data]) => {
      let base = (window as unknown as { mollieAdvancedSettingsAjaxUrl: string }).mollieAdvancedSettingsAjaxUrl;
      if (location.protocol === 'https:' && base.startsWith('http:')) {
        base = base.replace('http:', 'https:');
      }
      const url = new URL(base, location.origin);
      url.searchParams.set('ajax', '1');

      const formData = new FormData();
      formData.append('action', action);
      if (data !== undefined) {
        formData.append('data', JSON.stringify(data));
      }
      const response = await fetch(url.toString(), { method: 'POST', body: formData });

      return response.json();
    },
    [action, data] as const
  );

const openForm = async (page: Page) => {
  const settings = new AdvancedSettingsPage(page);
  await settings.goto();

  const rendered = await settings.waitForForm();
  skipIfDisconnected(!rendered, 'the advanced settings form did not render');

  return settings;
};

/**
 * The status sections are collapsed by default and reopen when a save fails on
 * one of their fields, so the save endpoint must name the rejected config keys
 * in `fieldErrors` and must not write the rejected value.
 */
test('save rejects an unknown order status and reports the field', async ({ page }) => {
  await openForm(page);

  const readOpenStatus = async () => {
    const json = await callAjax(page, 'getSettings');
    const mappings: StatusMapping[] | undefined = json.data?.statusMappings;
    test.skip(mappings === undefined, 'getSettings returned no data, module disconnected');

    return mappings?.find((mapping) => mapping.configKey === 'MOLLIE_STATUS_OPEN')?.prestashopStatus;
  };

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
