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
import {useMappedState} from 'redux-react-hook';

import OrderPanelContent from '@transaction/components/orderlines/OrderPanelContent';
import WarningContent from '@transaction/components/orderlines/WarningContent';

export default function OrderPanel(): ReactElement<{}> {
    const {config: {legacy, moduleDir}, config} = useCallback(useMappedState((state: IMollieOrderState): any => ({
        translations: state.translations,
        config: state.config,
        order: state.order,
    })), []);

    if (Object.keys(config).length <= 0) {
        return null;
    }

    if (legacy) {
        return (
            <fieldset style={{marginTop: '14px'}}>
                <legend>
                    <img
                        src={`${moduleDir}views/img/logo_small.png`}
                        width="32"
                        height="32"
                        alt="Mollie logo"
                        style={{height: '16px', width: '16px', opacity: 0.8}}
                    />
                    &nbsp;<span>Mollie</span>&nbsp;
                </legend>
                <WarningContent/>
                <OrderPanelContent/>
            </fieldset>
        );
    }

    return (
        <div className="panel card">
            <div className="panel-heading card-header">
                <img
                    src={`${moduleDir}views/img/mollie_panel_icon.png`}
                    width="32"
                    height="32"
                    alt="Mollie logo"
                    style={{height: '16px', width: '16px', opacity: 0.8}}
                />
                &nbsp;<span>Mollie</span>&nbsp;
            </div>
            <WarningContent/>
            <OrderPanelContent/>
        </div>
    );
}
