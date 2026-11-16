import type { Page } from '@playwright/test';

export type StatusMapping = { configKey: string; prestashopStatus: string; mollieStatus: string; allowSkip?: boolean };

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
