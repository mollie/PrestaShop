/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { lazy, ReactElement, Suspense, useMemo } from 'react';
import { StoreContext } from 'redux-react-hook';

import store from '@transaction/store';

const RefundPanel = lazy(() => import(/* webpackChunkName: "transactionRefund" */ '@transaction/components/refund/RefundPanel'));
const OrderPanel = lazy(() => import(/* webpackChunkName: "transactionOrder" */ '@transaction/components/orderlines/OrderPanel'));

export default function MolliePanel(): ReactElement<{}> {
  const { payment, order }: Partial<IMollieOrderState> = useMemo(() => store.getState(), []) as any;

  return (
    <StoreContext.Provider value={store}>
      <>
        {payment && (
          <Suspense fallback={null}>
            <RefundPanel/>
          </Suspense>
        )}
        {order && (
          <Suspense fallback={null}>
            <OrderPanel/>
          </Suspense>
        )}
      </>
    </StoreContext.Provider>
  );
}
