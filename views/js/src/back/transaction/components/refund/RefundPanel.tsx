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
import { useMappedState } from 'redux-react-hook';

import PaymentInfo from '@transaction/components/refund/PaymentInfo';
import RefundInfo from '@transaction/components/refund/RefundInfo';
import LoadingDots from '@shared/components/LoadingDots';
import WarningContent from "@transaction/components/orderlines/WarningContent";

export default function RefundPanel(): ReactElement<{}> {
  const { payment, config }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    config: state.config,
    payment: state.payment,
  }),);

  if (Object.keys(config).length <= 0) {
    return null;
  }
  const { moduleDir, legacy } = config;

  if (legacy) {
    return (
      <fieldset style={{ marginTop: '14px' }}>
        <legend className="panel-heading card-header">
          <img
            src={`${moduleDir}views/img/logo_small.png`}
            width="32"
            height="32"
            alt=""
            style={{ height: '16px', width: '16px', opacity: 0.8 }}
          />
          <span>Mollie</span>&nbsp;
        </legend>
        <WarningContent/>
        {!payment && <LoadingDots/>}
        {!!payment && payment.status && (
          <>
            <PaymentInfo/>
            <RefundInfo/>
          </>
        )}
      </fieldset>
    );
  }

  return (
    <div className="panel card">
      <div className="panel-heading card-header">
        <img
          src={`${moduleDir}views/img/mollie_panel_icon.png`}
          width="32"
          height="32"
          alt=""
          style={{ height: '16px', width: '16px', opacity: 0.8 }}
        /> <span>Mollie</span>&nbsp;
      </div>
      <WarningContent/>
      {!payment && <LoadingDots/>}
      {!!payment && payment.status && (
        <div className="panel-body card-body row">
          <PaymentInfo/>
          <RefundInfo/>
        </div>
      )}
    </div>
  );
}

