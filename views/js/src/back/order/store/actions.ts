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
// Action types
export enum ReduxActionTypes {
  updateTranslations = 'UPDATE_MOLLIE_ORDER_TRANSLATIONS',
  updateConfig = 'UPDATE_MOLLIE_ORDER_CONFIG',
  updateOrder = 'UPDATE_MOLLIE_ORDER',
  updatePayment = 'UPDATE_MOLLIE_PAYMENT',
  updateCurrencies = 'UPDATE_MOLLIE_CURRENCIES',
  updateViewportWidth = 'UPDATE_MOLLIE_VIEWPORT_WIDTH',
}

// Action creators
declare global {
  interface IUpdateTranslationsAction {
    type: string,
    translations: ITranslations,
  }

  interface IUpdateConfigAction {
    type: string,
    config: IMollieOrderConfig,
  }

  interface IUpdateOrderAction {
    type: string,
    order: IMollieApiOrder,
  }

  interface IUpdatePaymentAction {
    type: string,
    payment: IMollieApiPayment,
  }

  interface IUpdateCurrenciesAction {
    type: string,
    currencies: ICurrencies,
  }

  interface IUpdateViewportWidthAction {
    type: string,
    width: number,
  }
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

export function updateViewportWidth(width: number) : IUpdateViewportWidthAction {
  return { type: ReduxActionTypes.updateViewportWidth, width };
}
