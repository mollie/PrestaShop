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
import React, { ReactElement, useCallback, useState } from 'react';
import xss from 'xss';
import { get } from 'lodash';

import { updatePayment } from '@transaction/store/actions';
import RefundButton from '@transaction/components/refund/RefundButton';
import PartialRefundButton from '@transaction/components/refund/PartialRefundButton';
import { refundPayment as refundPaymentAjax } from '@transaction/misc/ajax';
import { formatCurrency } from '@shared/tools';
import { IMollieApiPayment } from '@shared/globals';
import { SweetAlert } from 'sweetalert/typings/core';
import { useDispatch, useMappedState } from 'redux-react-hook';

export default function RefundForm(): ReactElement<{}> {
  const [loading, setLoading] = useState<boolean>(false);
  const [refundInput, setRefundInput] = useState<string>('');
  const { translations, payment: { id: transactionId }, payment, currencies, config: { legacy } }: Partial<IMollieOrderState> = useCallback(useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
    config: state.config,
    payment: state.payment,
    currencies: state.currencies,
  }),), []);
  const dispatch = useDispatch();
  const _dispatchUpdatePayment = (payment: IMollieApiPayment) => useCallback(() => dispatch(updatePayment(payment)), []);

  async function _refundPayment(partial = false): Promise<boolean> {
    let amount;
    if (partial) {
      amount = parseFloat(refundInput.replace(/[^0-9.,]/g, '').replace(',', '.'));
      if (isNaN(amount)) {
        import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert').then(({ default: swal }) => {
          swal({
            icon: 'error',
            title: translations.invalidAmount,
            text: translations.notAValidAmount,
          }).then();
        });

        return false;
      }
    }

    const { default: swal } = await import(/* webpackPrefetch: true, webpackChunkName: "sweetalert" */ 'sweetalert') as never as { default: SweetAlert };
    const input = await swal({
      dangerMode: true,
      icon: 'warning',
      title: xss(translations.areYouSure),
      text: xss(translations.areYouSureYouWantToRefund),
      buttons: {
        cancel: {
          text: xss(translations.cancel),
          visible: true,
        },
        confirm: {
          text: xss(translations.refund),
        },
      },
    });
    if (input) {
      try {
        setLoading(true);
        const { success = false, payment = null } = await refundPaymentAjax(transactionId, amount);
        if (success) {
          if (payment) {
            _dispatchUpdatePayment(payment);
            setRefundInput('');
          }
        } else {
          swal({
            icon: 'error',
            title: translations.refundFailed,
            text: translations.unableToRefund,
          }).then();
        }
      } catch (e) {
        console.error(e);
      } finally {
        setLoading(false);
      }
    }
  }

  if (legacy) {
    return (
      <>
        <h3>{translations.refund}</h3>
        <span>
          <RefundButton
            refundPayment={_refundPayment}
            loading={loading}
            disabled={parseFloat(payment.settlementAmount.value) <= parseFloat(payment.amountRefunded.value)}
          />
          <span>
            {translations.remaining}:
          </span>
          <input
            type="text"
            placeholder={formatCurrency(parseFloat(payment.amountRemaining.value), get(currencies, payment.amountRemaining.currency))}
            disabled={loading}
            value={refundInput}
            onChange={({ target: { value } }: any) => setRefundInput(value)}
            style={{
              width: '80px',
              height: '15px',
              margin: '-2px 4px 0 4px',
            }}
          />
          <PartialRefundButton
            refundPayment={_refundPayment}
            loading={loading}
            disabled={parseFloat(payment.amountRemaining.value) <= 0}
          />
        </span>
      </>
    );
  }

  return (
    <>
      <h4>{translations.refund}</h4>
      <div className="well well-sm">
        <div className="form-inline">
          <div className="form-group">
            <RefundButton
              refundPayment={_refundPayment}
              loading={loading}
              disabled={parseFloat(payment.settlementAmount.value) <= parseFloat(payment.amountRefunded.value)}
            />
          </div>
          <div className="form-group">
            <div className="input-group" style={{ minWidth: '100px' }}>
              <div className="input-group-addon">
                {translations.remaining}:
              </div>
              <input
                type="text"
                className="form-control"
                placeholder={formatCurrency(parseFloat(payment.amountRemaining.value), get(currencies, payment.amountRemaining.currency))}
                disabled={loading}
                value={refundInput}
                onChange={({ target: { value } }: any) => setRefundInput(value)}
                style={{ width: '80px' }}
              />
              <PartialRefundButton
                refundPayment={_refundPayment}
                loading={loading}
                disabled={parseFloat(payment.amountRemaining.value) <= 0}
              />
            </div>
          </div>
        </div>
      </div>
    </>
  );
}

