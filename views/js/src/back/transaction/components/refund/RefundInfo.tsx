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
import styled from 'styled-components';

import RefundHistory from '@transaction/components/refund/RefundHistory';
import RefundForm from '@transaction/components/refund/RefundForm';
import { useMappedState } from 'redux-react-hook';

const Div = styled.div`
@media only screen and (min-width: 992px) {
  margin-left: 5px!important;
  margin-right: -5px!important;
}
` as any;

export default function RefundInfo(): ReactElement<{}> {
  const { translations, config: { legacy }, payment }: Partial<IMollieOrderState> = useMappedState( (state: IMollieOrderState): any => ({
    payment: state.payment,
    translations: state.translations,
    config: state.config,
  }));

  if (legacy) {
    return (
      <>
        <h3>{translations.refunds}</h3>
        {payment.amountRefunded && <RefundHistory/>}
        {payment.amountRefunded && <RefundForm/>}
        {!payment.amountRefunded && <div className="warn">{translations.refundsAreCurrentlyUnavailable}</div>}
      </>
    );
  }

  return (
    <Div className="col-md-9">
      <div className="panel card">
        <div className="panel-heading card-header">{translations.refunds}</div>
        <div className="card-body">
          {payment.amountRefunded && <RefundHistory/>}
          {payment.amountRefunded && <RefundForm/>}
          {!payment.amountRefunded &&
          <div className="alert alert-warning">{translations.refundsAreCurrentlyUnavailable}</div>}
        </div>
      </div>
    </Div>
  );
}
