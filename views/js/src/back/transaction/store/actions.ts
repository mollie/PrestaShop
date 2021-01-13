/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
// Action types
import { ICurrencies, IMollieApiOrder, IMollieApiPayment, IMollieOrderConfig, ITranslations } from '@shared/globals';

export enum ReduxActionTypes {
  updateTranslations = 'UPDATE_MOLLIE_ORDER_TRANSLATIONS',
  updateConfig = 'UPDATE_MOLLIE_ORDER_CONFIG',
  updateOrder = 'UPDATE_MOLLIE_ORDER',
  updatePayment = 'UPDATE_MOLLIE_PAYMENT',
  updateWarning = 'UPDATE_MOLLIE_WARNING',
  updateCurrencies = 'UPDATE_MOLLIE_CURRENCIES',
  updateViewportWidth = 'UPDATE_MOLLIE_VIEWPORT_WIDTH',
}

// Action creators
export interface IUpdateTranslationsAction {
  type: string;
  translations: ITranslations;
}

export interface IUpdateConfigAction {
  type: string;
  config: IMollieOrderConfig;
}

export interface IUpdateOrderAction {
  type: string;
  order: IMollieApiOrder;
}

export interface IUpdatePaymentAction {
  type: string;
  payment: IMollieApiPayment;
}

export interface IUpdateWarningAction {
  type: string;
  orderWarning: string;
}

export interface IUpdateCurrenciesAction {
  type: string;
  currencies: ICurrencies;
}

export interface IUpdateViewportWidthAction {
  type: string;
  width: number;
}

export function updateTranslations(translations: ITranslations): IUpdateTranslationsAction {
  return { type: ReduxActionTypes.updateTranslations, translations };
}

export function updateCurrencies(currencies: ICurrencies): IUpdateCurrenciesAction {
  return { type: ReduxActionTypes.updateCurrencies, currencies };
}

export function updateConfig(config: IMollieOrderConfig): IUpdateConfigAction {
  return { type: ReduxActionTypes.updateConfig, config };
}

export function updateOrder(order: IMollieApiOrder): IUpdateOrderAction {
  return { type: ReduxActionTypes.updateOrder, order };
}

export function updatePayment(payment: IMollieApiPayment): IUpdatePaymentAction {
  return { type: ReduxActionTypes.updatePayment, payment };
}

export function updateViewportWidth(width: number): IUpdateViewportWidthAction {
  return { type: ReduxActionTypes.updateViewportWidth, width };
}

export function updateWarning(status: string): IUpdateWarningAction {
  return { type: ReduxActionTypes.updateWarning, orderWarning: status };
}