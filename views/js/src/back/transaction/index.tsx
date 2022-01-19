/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { lazy, Suspense } from 'react';
import { render } from 'react-dom';
import { StoreContext } from 'redux-react-hook'
import { throttle } from 'lodash';

import { ICurrencies, IMollieOrderConfig, ITranslations } from '@shared/globals';

import LoadingDots from '@shared/components/LoadingDots';
import {updateWarning} from "@transaction/store/actions";

const MolliePanel = lazy(() => import('@transaction/components/MolliePanel'));

export default function transactionInfo (
  target: any,
  config: IMollieOrderConfig,
  translations: ITranslations,
  currencies: ICurrencies
): void {
  (async function () {
    const [
      { default: store },
      {
        updateConfig,
        updateCurrencies,
        updateOrder,
        updatePayment,
        updateTranslations,
        updateViewportWidth,
      },
      { retrieveOrder, retrievePayment },
    ] = await Promise.all([
      import(/* webpackPrefetch: true, webpackChunkName: "transaction" */ '@transaction/store'),
      import(/* webpackPrefetch: true, webpackChunkName: "transaction" */ '@transaction/store/actions'),
      import(/* webpackPrefetch: true, webpackChunkName: "transaction" */ '@transaction/misc/ajax'),
    ]);

    // Listen for window resizes
    window.addEventListener('resize', throttle(() => {
      store.dispatch(updateViewportWidth(window.innerWidth));
    }, 200));

    store.dispatch(updateCurrencies(currencies));
    store.dispatch(updateTranslations(translations));
    store.dispatch(updateConfig(config));

    const { transactionId } = config;
    if (transactionId.substr(0, 3) === 'ord') {
      store.dispatch(updateOrder(await retrieveOrder(transactionId)));
    } else {
      store.dispatch(updatePayment(await retrievePayment(transactionId)));
    }

    render(
      <StoreContext.Provider value={store}>
        <Suspense fallback={<LoadingDots/>}>
          <MolliePanel/>
        </Suspense>
      </StoreContext.Provider>,
      typeof target === 'string' ? document.querySelector(target) : target
    );
  }());
};
