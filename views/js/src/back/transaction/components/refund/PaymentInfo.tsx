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
import { useMappedState } from 'redux-react-hook';

import PaymentInfoContent from '@transaction/components/refund/PaymentInfoContent';

const Div = styled.div`
@media only screen and (min-width: 992px) {
  margin-left: -5px!important;
  margin-right: 5px!important;
}
` as any;

export default function PaymentInfo(): ReactElement<{}> {
  const { translations, config: { legacy } } = useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
    config: state.config,
  }));

  if (legacy) {
    return (
      <>
        <PaymentInfoContent/>
        <br/>
      </>
    );
  }

  return (
    <Div className="col-md-3">
      <div className="panel card">
        <div className="panel-heading card-header">{translations.paymentInfo}</div>
        <div className="card-body">
          <PaymentInfoContent/>
        </div>
      </div>
    </Div>
  );
}
