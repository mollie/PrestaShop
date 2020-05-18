/**
 * Copyright (c) 2012-2020, Mollie B.V.
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

