/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { ReactElement, useCallback, useState } from 'react';
import xss from 'xss';
import { get } from 'lodash';

import { updatePayment } from '@transaction/store/actions';
import { updateWarning } from '@transaction/store/actions';
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
  const { translations, payment: { id: transactionId }, payment, currencies, config: { legacy } }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
    config: state.config,
    payment: state.payment,
    currencies: state.currencies,
  }),);
  const dispatch = useDispatch();

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
            dispatch(updateWarning('refunded'));
            dispatch(updatePayment(payment));
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
            {translations.refundable}:
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
  let html;
  if (payment.settlementAmount) {
    html = (<RefundButton
        refundPayment={_refundPayment}
        loading={loading}
        disabled={parseFloat(payment.settlementAmount.value) <= parseFloat(payment.amountRefunded.value)}
    />);
  } else {
    html = '';
  }
  return (
    <>
      <h4>{translations.refund}</h4>
      <div className="well well-sm">
        <div className="form-inline">
          <div className="form-group">
            {html}
          </div>
          <div className="form-group">
            <div className="input-group" style={{ minWidth: '100px' }}>
              <div className="input-group-addon input-group-prepend">
                <span className="input-group-text">{translations.refundable}:</span>
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

