import { test, type Page } from '@playwright/test';
import { AdvancedSettingsPage } from '../pages/admin/advanced-settings-page';
import { skipIfDisconnected } from './module-state';

export type StatusMapping = { configKey: string; prestashopStatus: string; mollieStatus: string };

export const callAdvancedSettingsAjax = (page: Page, action: string, data?: Record<string, unknown>) =>
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

export const openAdvancedSettingsForm = async (page: Page) => {
  const settings = new AdvancedSettingsPage(page);
  await settings.goto();

  const rendered = await settings.waitForForm();
  skipIfDisconnected(!rendered, 'the advanced settings form did not render');

  return settings;
};

export const findStatusMapping = async (page: Page, configKey: string): Promise<StatusMapping | undefined> => {
  const json = await callAdvancedSettingsAjax(page, 'getSettings');
  const mappings: StatusMapping[] | undefined = json.data?.statusMappings;
  test.skip(mappings === undefined, 'getSettings returned no data, module disconnected');

  return mappings?.find((mapping) => mapping.configKey === configKey);
};
