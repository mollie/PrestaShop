import axios from 'axios';
import store from '../store';
import _ from 'lodash';

export const retrievePayment = async (transactionId: number): Promise<IMollieApiPayment|null> => {
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
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data } = await axios.post(ajaxEndpoint, {
      resource: 'payments',
      action: 'refund',
      transactionId,
      amount,
    });
    return _.defaults(data, { success: false, payment: null });
  } catch (e) {
    console.error(e);

    return false;
  }
};

export const refundOrder = async (transactionId: string, orderLines?: Array<IMollieOrderLine>): Promise<any> => {
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data } = await axios.post(ajaxEndpoint, {
      resource: 'orders',
      action: 'refund',
      transactionId,
      orderLines,
    });
    return _.defaults(data, { success: false, order: null });
  } catch (e) {
    console.error(e);

    return false;
  }
};

export const cancelOrder = async (transactionId: string, orderLines?: Array<IMollieOrderLine>): Promise<any> => {
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data } = await axios.post(ajaxEndpoint, {
      resource: 'orders',
      action: 'cancel',
      transactionId,
      orderLines,
    });
    return _.defaults(data, { success: false, order: null });
  } catch (e) {
    console.error(e);

    return false;
  }
};

export const shipOrder = async (transactionId: string, orderLines?: Array<IMollieOrderLine>, tracking?: IMollieTracking): Promise<any> => {
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data } = await axios.post(ajaxEndpoint, {
      resource: 'orders',
      action: 'ship',
      transactionId,
      orderLines,
      tracking,
    });
    return _.defaults(data, { success: false, order: null });
  } catch (e) {
    console.error(e);

    return false;
  }
};
