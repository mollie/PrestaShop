/**
 * Copyright (c) 2012-2019, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 */
import axios from '@shared/axios';
import store from '../store';
import { defaults } from 'lodash';

import { IMollieApiOrder, IMollieApiPayment, IMollieOrderLine, IMollieTracking } from '../../../globals';

export const retrievePayment = async (transactionId: string): Promise<IMollieApiPayment|null> => {
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

export const refundOrder = async (transactionId: string, orderLines?: Array<IMollieOrderLine>): Promise<any> => {
  try {
    const ajaxEndpoint = store.getState().config.ajaxEndpoint;

    const { data } = await axios.post(ajaxEndpoint, {
      resource: 'orders',
      action: 'refund',
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

export const cancelOrder = async (transactionId: string, orderLines?: Array<IMollieOrderLine>): Promise<any> => {
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
