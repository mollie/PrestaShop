import { test, expect } from '../../fixtures/base';
import type { Page } from '@playwright/test';
import { AdvancedSettingsPage } from '../../pages/admin/advanced-settings-page';
import { skipIfDisconnected } from '../../helpers/module-state';
import { callAdvancedSettingsAjax, type StatusMapping } from '../../helpers/advanced-settings';

const SKIP_LABEL = 'Skip this status';
const SKIP_STATUS_ID = '0';
const WITHOUT_SKIP = ['MOLLIE_STATUS_AWAITING', 'MOLLIE_STATUS_OPEN'];

const openStatusMapping = async (page: Page) => {
  const settings = new AdvancedSettingsPage(page);
  await settings.goto();

  const rendered = await settings.waitForForm();
  skipIfDisconnected(!rendered, 'the advanced settings form did not render');

  await settings.statusMappingHeader().click();
  await expect(settings.statusMappingHeader()).toHaveAttribute('aria-expanded', 'true');

  return settings;
};

const readMappings = async (page: Page): Promise<StatusMapping[]> => {
  const json = await callAdvancedSettingsAjax(page, 'getSettings');
  const mappings: StatusMapping[] | undefined = json.data?.statusMappings;
  test.skip(mappings === undefined, 'getSettings returned no data, module disconnected');

  return mappings ?? [];
};

test('every status mapping except Awaiting and Open offers "Skip this status" first', async ({ page }) => {
  const settings = await openStatusMapping(page);
  const mappings = await readMappings(page);
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
  const settings = new AdvancedSettingsPage(page);
  await settings.goto();
  skipIfDisconnected(!(await settings.waitForForm()), 'the advanced settings form did not render');

  await page.locator('.multi-select-button').first().click();
  const labels = await page.locator('.radio-select-option-label').allInnerTexts();

  expect(labels.length).toBeGreaterThan(0);
  expect(labels).not.toContain(SKIP_LABEL);
});

test('a skipped status saves as 0 and is shown as "Skip this status" after reload', async ({ page }) => {
  const configKey = 'MOLLIE_STATUS_CHARGEBACK';
  const settings = await openStatusMapping(page);
  const original = (await readMappings(page)).find((mapping) => mapping.configKey === configKey);
  expect(original).toBeDefined();

  try {
    const row = settings.statusMappingRow(configKey);
    await row.locator('.radio-select-button').click();
    await row.locator('.radio-select-option', { hasText: SKIP_LABEL }).click();
    await expect(row.locator('.radio-select-button')).toHaveText(SKIP_LABEL);

    await settings.save();
    await settings.expectSavedSuccessfully();

    const saved = (await readMappings(page)).find((mapping) => mapping.configKey === configKey);
    expect(saved?.prestashopStatus).toBe(SKIP_STATUS_ID);

    await openStatusMapping(page);
    await expect(settings.statusMappingRow(configKey).locator('.radio-select-button')).toHaveText(SKIP_LABEL);
  } finally {
    await callAdvancedSettingsAjax(page, 'saveSettings', { statusMappings: [original] });
  }
});
