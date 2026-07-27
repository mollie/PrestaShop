import { test as setup } from '@playwright/test';
import { setMethodConfig } from '../helpers/config';
import { paymentMethods } from '../data/payment-methods';

setup('configure Payments API methods', async () => {
  for (const method of paymentMethods) {
    if (method.apis.includes('payments')) {
      setMethodConfig(method.id, { enabled: true, api: 'payments' });
    }
  }
});
