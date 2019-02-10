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
import { combineReducers } from 'redux';
import {
  IUpdateConfigAction,
  IUpdateCurrenciesAction,
  IUpdateOrderAction,
  IUpdatePaymentAction,
  IUpdateTranslationsAction,
  IUpdateViewportWidthAction,
  ReduxActionTypes
} from '@transaction/store/actions';
import { ICurrencies, IMollieApiOrder, IMollieApiPayment, IMollieOrderConfig, ITranslations } from '@shared/globals';

declare global {
  interface IMollieOrderState {
    translations: ITranslations;
    config: IMollieOrderConfig;
    viewportWidth: number;
    order: IMollieApiOrder;
    payment: IMollieApiPayment;
    currencies: ICurrencies;
  }
}

const translations = (state: any = {}, action: IUpdateTranslationsAction): ITranslations => {
  switch (action.type) {
    case ReduxActionTypes.updateTranslations:
      return action.translations;
    default:
      return state;
  }
};

const config = (state: any = {}, action: IUpdateConfigAction): IMollieOrderConfig => {
  switch (action.type) {
    case ReduxActionTypes.updateConfig:
      return action.config;
    default:
      return state;
  }
};

const order = (state: IMollieApiOrder = null, action: IUpdateOrderAction): IMollieApiOrder => {
  switch (action.type) {
    case ReduxActionTypes.updateOrder:
      return action.order;
    default:
      return state;
  }
};

const payment = (state: IMollieApiPayment = null, action: IUpdatePaymentAction): IMollieApiPayment => {
  switch (action.type) {
    case ReduxActionTypes.updatePayment:
      return action.payment;
    default:
      return state;
  }
};

const currencies = (state: ICurrencies = {}, action: IUpdateCurrenciesAction): ICurrencies => {
  switch (action.type) {
    case ReduxActionTypes.updateCurrencies:
      return action.currencies;
    default:
      return state;
  }
};

const initialViewportwidth = window.innerWidth;
const viewportWidth = (state = initialViewportwidth, action : IUpdateViewportWidthAction): number => {
  switch (action.type) {
    case ReduxActionTypes.updateViewportWidth:
      return action.width;
    default:
      return state;
  }
};

const checkoutApp = combineReducers({
  translations,
  config,
  order,
  payment,
  currencies,
  viewportWidth,
});

export default checkoutApp;
