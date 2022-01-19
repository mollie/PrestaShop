/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { ReactElement, useCallback } from 'react';
import cx from 'classnames';

import PaymentInfo from '@transaction/components/orderlines/PaymentInfo';
import OrderLinesInfo from '@transaction/components/orderlines/OrderLinesInfo';
import LoadingDots from '@shared/components/LoadingDots';
import { useMappedState } from 'redux-react-hook';

export default function OrderPanelContent(): ReactElement<{}> {
  const { order, config: { legacy } }: Partial<IMollieOrderState> = useCallback(useMappedState( (state: IMollieOrderState): any => ({
    order: state.order,
    config: state.config,
  })), []);

  return (
    <>
      {!order && <LoadingDots/>}
      {!!order && order.status && (
        <div className={
          cx({
            'panel-body card-body': !legacy,
            'row': !legacy,
          })}
        >
          <PaymentInfo/>
          <OrderLinesInfo/>
        </div>
      )}
    </>
  );
}
