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
import cx from 'classnames';

import PaymentInfo from '@transaction/components/orderlines/PaymentInfo';
import OrderLinesInfo from '@transaction/components/orderlines/OrderLinesInfo';
import LoadingDots from '@shared/components/LoadingDots';
import {useMappedState} from 'redux-react-hook';

export default function WarningContent(): ReactElement<{}> {

    const {orderWarning, translations} = useMappedState((state): any => ({
        orderWarning: state.orderWarning,
        translations: state.translations,
    }));

    let message = '';
    switch (orderWarning) {
        case "refunded" :
            message = translations.refundSuccessMessage;
            break;
        case "shipped":
            message = translations.shipmentWarning;
            break;
        case "canceled":
            message = translations.cancelWarning;
            break;
        default:
            message = '';
    }

    if (!message) {
        return (
            <>
            </>
        );
    }

    return (
        <>
            <div className="alert alert-success">{message}</div>
        </>
    );
}
