/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import { combineReducers } from 'redux';
import {
  IUpdateConfigAction,
  IUpdateCurrenciesAction,
  IUpdateOrderAction,
  IUpdatePaymentAction,
  IUpdateTranslationsAction,
  IUpdateViewportWidthAction, IUpdateWarningAction,
  ReduxActionTypes
} from '@transaction/store/actions';
import {
  ICurrencies,
  IMollieApiOrder,
  IMollieApiPayment,
  IMollieOrderConfig,
  IMollieOrderDetails,
  ITranslations
} from '@shared/globals';

declare global {
  interface IMollieOrderState {
    translations: ITranslations;
    config: IMollieOrderConfig;
    viewportWidth: number;
    order: IMollieApiOrder;
    payment: IMollieApiPayment;
    currencies: ICurrencies;
    orderWarning: string;
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
const viewportWidth = (state = initialViewportwidth, action: IUpdateViewportWidthAction): number => {
  switch (action.type) {
    case ReduxActionTypes.updateViewportWidth:
      return action.width;
    default:
      return state;
  }
};

const orderWarning = (state: any = {}, action: IUpdateWarningAction): string => {
  switch (action.type) {
    case ReduxActionTypes.updateWarning:
      return action.orderWarning;
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
  orderWarning,
});

export default checkoutApp;
