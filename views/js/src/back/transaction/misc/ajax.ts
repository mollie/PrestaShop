/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import axios from '@shared/axios';
import { defaults } from 'lodash';

import { IMollieApiOrder, IMollieApiPayment, IMollieOrderLine, IMollieTracking } from '@shared/globals';

export const retrievePayment = async (transactionId: string): Promise<IMollieApiPayment|null> => {
  const [
    { default: store },
  ] = await Promise.all([
    import(/* webpackPrefetch: true, webpackChunkName: "transaction" */ '@transaction/store'),
  ]);
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data: { payment } = { payment: null } } = await axios.post(ajaxEndpoint, {
      resource: 'payments',
      action: 'retrieve',
      transactionId,
    });

    return payment || null;
  } catch (e) {
    console.error(e);

    return null;
  }
};

export const retrieveOrder = async (transactionId: string): Promise<IMollieApiOrder|null> => {
  const [
    { default: store }
  ] = await Promise.all([
    import(/* webpackPrefetch: true, webpackChunkName: "transaction" */ '@transaction/store'),
  ]);
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data: { order } = { order: null } } = await axios.post(ajaxEndpoint, {
      resource: 'orders',
      action: 'retrieve',
      transactionId,
    });

    return order || null;
  } catch (e) {
    console.error(e);

    return null;
  }
};

export const refundPayment = async (transactionId: string, amount?: number): Promise<any> => {
  const [
    { default: store },
  ] = await Promise.all([
      import(/* webpackPrefetch: true, webpackChunkName: "transaction" */ '@transaction/store'),
  ]);
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data } = await axios.post(ajaxEndpoint, {
      resource: 'payments',
      action: 'refund',
      transactionId,
      amount,
    });
    if (!data.success && typeof data.message === 'string') {
      throw data.detailed ? data.detailed : data.message;
    }

    return defaults(data, { success: false, payment: null });
  } catch (e) {
    if (typeof e === 'string') {
      throw e;
    }
    console.error(e);

    return false;
  }
};

export const refundOrder = async (order: IMollieApiOrder, orderLines?: Array<IMollieOrderLine>): Promise<any> => {
  const [
    { default: store},
  ] = await Promise.all([
    import(/* webpackPrefetch: true, webpackChunkName: "transaction" */ '@transaction/store'),
  ]);
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data } = await axios.post(ajaxEndpoint, {
      resource: 'orders',
      action: 'refund',
      orderLines,
      order
    });
    if (!data.success && typeof data.message === 'string') {
      throw data.detailed ? data.detailed : data.message;
    }
    return defaults(data, { success: false, order: null });
  } catch (e) {
    if (typeof e === 'string') {
      throw e;
    }
    console.error(e);

    return false;
  }
};

export const cancelOrder = async (transactionId: string, orderLines?: Array<IMollieOrderLine>): Promise<any> => {
  const [
    { default: store },
  ] = await Promise.all([
    import(/* webpackPrefetch: true, webpackChunkName: "transaction" */ '@transaction/store'),
  ]);
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data } = await axios.post(ajaxEndpoint, {
      resource: 'orders',
      action: 'cancel',
      transactionId,
      orderLines,
    });
    if (!data.success && typeof data.message === 'string') {
      throw data.detailed ? data.detailed : data.message;
    }
    return defaults(data, { success: false, order: null });
  } catch (e) {
    if (typeof e === 'string') {
      throw e;
    }
    console.error(e);

    return false;
  }
};

export const shipOrder = async (transactionId: string, orderLines?: Array<IMollieOrderLine>, tracking?: IMollieTracking): Promise<any> => {
  const [
    { default: store },
  ] = await Promise.all([
    import(/* webpackPrefetch: true, webpackChunkName: "transaction" */ '@transaction/store'),
  ]);
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data } = await axios.post(ajaxEndpoint, {
      resource: 'orders',
      action: 'ship',
      transactionId,
      orderLines,
      tracking,
    });
    if (!data.success && typeof data.message === 'string') {
      throw data.detailed ? data.detailed : data.message;
    }

    return defaults(data, { success: false, order: null });
  } catch (e) {
    if (typeof e === 'string') {
      throw e;
    }
    console.error(e);

    return false;
  }
};
