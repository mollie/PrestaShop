/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, {ReactElement, useCallback} from 'react';
import moment from 'moment';
import {get} from 'lodash';

import {formatCurrency} from '@shared/tools';
import {useMappedState} from 'redux-react-hook';

export default function PaymentInfoContent(): ReactElement<{}> {
    const {translations, order, currencies, config: {legacy}}: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
        order: state.order,
        currencies: state.currencies,
        translations: state.translations,
        config: state.config,
    }));

    return (
        <>
            {legacy && <h3>{translations.transactionInfo}</h3>}
            {!legacy && <h4>{translations.transactionInfo}</h4>}
            <strong>{translations.transactionId}</strong>: <span>{order.id}</span><br/>
            <strong>{translations.method}</strong>: <span>{order.details.remainderMethod ? order.details.remainderMethod : order.method}</span><br/>
            <strong>{translations.date}</strong>: <span>{moment(order.createdAt).format('YYYY-MM-DD HH:mm:ss')}</span><br/>
            <strong>{translations.amount}</strong>: <span>{formatCurrency(parseFloat(order.amount.value), get(currencies, order.amount.currency))}</span><br/>
            <strong>{translations.refundable}</strong>: <span>{formatCurrency(parseFloat(order.availableRefundAmount.value), get(currencies, order.availableRefundAmount.currency))}</span><br/>
            {(order.details.remainderMethod || order.details.issuer) &&
            <>
                <br/>
                <h4>{translations.voucherInfo}</h4>
            </>
            }
            {order.details.issuer &&
            <>
                <span><strong>{translations.issuer}</strong>: <span>{order.details.issuer}</span><br/></span>
            </>
            }

            {order.details.vouchers &&
            order.details.vouchers.map(voucher =>
                <>
                    <span><strong>{translations.amount}</strong>: <span>{formatCurrency(parseFloat(voucher.amount.value), get(currencies, voucher.amount.currency))}</span><br/></span>
                </>
            )
            }
        </>
    );
}
