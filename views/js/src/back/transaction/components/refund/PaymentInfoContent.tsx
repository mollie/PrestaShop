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
import moment from 'moment';
import { get } from 'lodash';
import { useMappedState } from 'redux-react-hook';

import { formatCurrency } from '@shared/tools';

export default function PaymentInfoContent(): ReactElement<{}> {
  const { translations, payment, currencies, config: { legacy } }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    payment: state.payment,
    currencies: state.currencies,
    translations: state.translations,
    config: state.config,
  }));

  return (
    <>
      {legacy && <h3>{translations.transactionInfo}</h3>}
      {!legacy && <h4>{translations.transactionInfo}</h4>}
      <strong>{translations.transactionId}</strong>: <span>{payment.id}</span><br/>
      <strong>{translations.date}</strong>: <span>{moment(payment.createdAt).format('YYYY-MM-DD HH:mm:ss')}</span><br/>
      <strong>{translations.amount}</strong>: <span>{formatCurrency(parseFloat(payment.amount.value), get(currencies, payment.amount.currency))}</span><br/>
    </>
  );
}
