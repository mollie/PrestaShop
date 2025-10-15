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

import EmptyRefundTable from '@transaction/components/refund/EmptyRefundTable';
import RefundTable from '@transaction/components/refund/RefundTable';

export default function RefundHistory(): ReactElement<{}> {
  const { translations, payment }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
    payment: state.payment,
  }));

  return (
    <>
      <h4>{translations.refundHistory}</h4>
      {!payment.refunds.length && <EmptyRefundTable/>}
      {!!payment.refunds.length && <RefundTable/>}
    </>
  );
}
