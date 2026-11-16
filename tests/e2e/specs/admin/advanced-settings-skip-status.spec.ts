import { test, expect } from '../../fixtures/base';
import type { Page } from '@playwright/test';
import {
  callAdvancedSettingsAjax,
  findStatusMapping,
  openAdvancedSettingsForm,
  type StatusMapping,
} from '../../helpers/advanced-settings';
import { getGlobalConfig, restoreGlobalConfig } from '../../helpers/config';

const SKIP_LABEL = 'Skip this status';
const SKIP_STATUS_ID = '0';
// Orders are created in these statuses, so they never offer the skip choice.
const WITHOUT_SKIP = [
  'MOLLIE_STATUS_AWAITING',
  'MOLLIE_STATUS_OPEN',
  'MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED',
  'MOLLIE_STATUS_PAID',
];

const openStatusMapping = async (page: Page) => {
  const settings = await openAdvancedSettingsForm(page);
  await settings.statusMappingHeader().click();
  await expect(settings.statusMappingHeader()).toHaveAttribute('aria-expanded', 'true');

  return settings;
};

test('every status mapping except the order creation ones offers "Skip this status" first', async ({ page }) => {
  const settings = await openStatusMapping(page);
  const json = await callAdvancedSettingsAjax(page, 'getSettings');
  const mappings: StatusMapping[] = json.data?.statusMappings ?? [];
  expect(mappings.length).toBeGreaterThan(WITHOUT_SKIP.length);

  for (const mapping of mappings) {
    const labels = await settings.statusMappingOptions(mapping.configKey);

    if (WITHOUT_SKIP.includes(mapping.configKey)) {
      expect(labels, mapping.configKey).not.toContain(SKIP_LABEL);
      continue;
    }

    expect(labels[0], mapping.configKey).toBe(SKIP_LABEL);
    expect(labels.filter((label) => label === SKIP_LABEL), mapping.configKey).toHaveLength(1);
  }
});

test('the auto-ship status picker does not offer "Skip this status"', async ({ page }) => {
  await openAdvancedSettingsForm(page);

  const options = page.locator('.dropdown-open .radio-select-option-label');
  // The button's centre can be a selected status tag or its remove button, so open it by the chevron.
  await page.locator('.multi-select-button .radio-select-icon').first().click();
  await options.first().waitFor();

  expect(await options.allInnerTexts()).not.toContain(SKIP_LABEL);
});

test('a skipped status saves as 0 and is shown as "Skip this status" after reload', async ({ page }) => {
  const configKey = 'MOLLIE_STATUS_CHARGEBACK';
  const previous = getGlobalConfig(configKey);
  const settings = await openStatusMapping(page);

  try {
    await settings.selectStatusMapping(configKey, SKIP_LABEL);
    await expect(settings.statusMappingButton(configKey)).toHaveText(SKIP_LABEL);

    await settings.save();
    await settings.expectSavedSuccessfully();
    expect((await findStatusMapping(page, configKey))?.prestashopStatus).toBe(SKIP_STATUS_ID);

    await openStatusMapping(page);
    await expect(settings.statusMappingButton(configKey)).toHaveText(SKIP_LABEL);
  } finally {
    restoreGlobalConfig(configKey, previous);
  }
});

test('save rejects "Skip this status" for a status orders are created in', async ({ page }) => {
  await openAdvancedSettingsForm(page);
  const paid = await findStatusMapping(page, 'MOLLIE_STATUS_PAID');
  expect(paid).toBeDefined();
  test.skip(paid?.prestashopStatus === SKIP_STATUS_ID, 'Paid is already stored as 0 on this shop');

  const result = await callAdvancedSettingsAjax(page, 'saveSettings', {
    statusMappings: [{ ...paid, prestashopStatus: SKIP_STATUS_ID }],
  });

  expect(result.success).toBe(false);
  expect(result.fieldErrors).toEqual(['MOLLIE_STATUS_PAID']);
  expect((await findStatusMapping(page, 'MOLLIE_STATUS_PAID'))?.prestashopStatus).toBe(paid?.prestashopStatus);
});
