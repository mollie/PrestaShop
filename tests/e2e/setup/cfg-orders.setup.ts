import { test as setup } from '@playwright/test';
import { setMethodConfig } from '../helpers/config';
import { paymentMethods } from '../data/payment-methods';

setup('configure Orders API methods', async () => {
  for (const method of paymentMethods) {
    if (method.apis.includes('orders')) {
      setMethodConfig(method.id, { enabled: true, api: 'orders' });
    }
  }
});
