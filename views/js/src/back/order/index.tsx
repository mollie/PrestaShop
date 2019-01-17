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
import React from 'react';
import { render } from 'react-dom';
import viewportSize from 'viewport-size';
import { throttle } from 'lodash';

import store from './store';
import { updateConfig, updateCurrencies, updateOrder, updatePayment, updateTranslations, updateViewportWidth } from './store/actions';
import MolliePanel from './components/MolliePanel';
import { retrieveOrder, retrievePayment } from './misc/ajax';
import { ICurrencies, IMollieOrderConfig, ITranslations } from '../../globals';
import { Provider } from 'react-redux';

export const orderInfo = (
  target: any,
  config: IMollieOrderConfig,
  translations: ITranslations,
  currencies: ICurrencies
) => {
  setTimeout(async () => {
    const { transactionId } = config;

    if (transactionId.substr(0, 3) === 'ord') {
      store.dispatch(updateOrder(await retrieveOrder(transactionId)));
    } else {
      store.dispatch(updatePayment(await retrievePayment(transactionId)));
    }
  }, 0);

  // Listen for window resizes
  window.addEventListener('resize', throttle(() => {
    store.dispatch(updateViewportWidth(viewportSize.getWidth()));
  }, 200));

  store.dispatch(updateCurrencies(currencies));
  store.dispatch(updateTranslations(translations));
  store.dispatch(updateConfig(config));

  return render(
    <Provider store={store}>
      <MolliePanel/>
    </Provider>,
    typeof target === 'string' ? document.querySelector(target) : target
  );
};


