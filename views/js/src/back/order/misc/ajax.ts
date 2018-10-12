import axios from 'axios';
import store from '../store';
import _ from 'lodash';

export const retrievePayment = async (orderId: number): Promise<false|IMollieApiPayment> => {
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data: { payment } = { payment: null } } = await axios.post(ajaxEndpoint, {
      resource: 'payments',
      action: 'retrieve',
      orderId,
    });

    return payment || false;
  } catch (e) {
    console.error(e);

    return false;
  }
};

export const retrieveOrder = async (transactionId: string): Promise<false|IMollieApiOrder> => {
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data: { order } = { order: null } } = await axios.post(ajaxEndpoint, {
      resource: 'orders',
      action: 'retrieve',
      orderId: transactionId,
    });

    return order || false;
  } catch (e) {
    console.error(e);

    return false;
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

export const refundOrder = async (transactionId: number, orderLines: Array<IMollieOrderLine>): Promise<boolean> => {
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
