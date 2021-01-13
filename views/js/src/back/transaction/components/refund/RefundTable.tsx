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
import RefundTableHeader from './RefundTableHeader';
import moment from 'moment';
import { get } from 'lodash';
import { useMappedState } from 'redux-react-hook';

import { formatCurrency } from '@shared/tools';
import { IMollieApiRefund } from '@shared/globals';

export default function RefundTable(): ReactElement<{}> {
  const { payment, currencies }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    payment: state.payment,
    currencies: state.currencies,
  }));

  return (
    <div className="table-responsive">
      <table className="table">
        <RefundTableHeader/>
        <tbody>
          {payment.refunds.map((refund: IMollieApiRefund) => (
            <tr key={refund.id} style={{ marginBottom: '100px' }}>
              <td style={{ width: '100px' }}><strong>{refund.id}</strong></td>
              <td>{moment(refund.createdAt).format('YYYY-MM-DD HH:mm:ss')}</td>
              <td>{formatCurrency(parseFloat(refund.amount.value), get(currencies, refund.amount.currency))}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

