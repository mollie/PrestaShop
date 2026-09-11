import { test as setup } from '@playwright/test';
import { setMethodConfig, clearMethodConfig } from '../helpers/config';
import { paymentMethods } from '../data/payment-methods';

setup('configure Orders API methods', async () => {
  // Start from a known state so the previous phase's assignments do not
  // leak into this one.
  clearMethodConfig();

  for (const method of paymentMethods) {
    if (method.apis.includes('orders')) {
      setMethodConfig(method.id, { enabled: true, api: 'orders' });
    }
  }
});
