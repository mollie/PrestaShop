/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, {ReactElement, useCallback, useState} from 'react';
import styled from 'styled-components';

import OrderLinesTable from '@transaction/components/orderlines/OrderLinesTable';
import EmptyOrderLinesTable from '@transaction/components/orderlines/EmptyOrderLinesTable';
import {ICurrencies, IMollieApiOrder, IMollieOrderConfig, ITranslations} from '@shared/globals';
import {useMappedState} from 'redux-react-hook';
import {formatCurrency} from "@shared/tools";
import {get} from "lodash";

interface IProps {
    // Redux
    translations?: ITranslations;
    order?: IMollieApiOrder;
    currencies?: ICurrencies;
    config?: IMollieOrderConfig;
}

const Div = styled.div`
@media only screen and (min-width: 992px) {
  margin-left: 5px!important;
  margin-right: -5px!important;
}
` as any;

export default function OrderLinesInfo(): ReactElement<{}> {
    const {translations, order, currencies, config: {legacy}}: IProps = useMappedState((state: IMollieOrderState): any => ({
        translations: state.translations,
        order: state.order,
        currencies: state.currencies,
        config: state.config,
    }));

    if (legacy) {
        return (
            <>
                {legacy && <h3>{translations.products}</h3>}
                {!legacy && <h4>{translations.products}</h4>}
                {!order || (!order.lines.length && <EmptyOrderLinesTable/>)}
                {!!order && !!order.lines.length && <OrderLinesTable/>}
            </>
        );
    }

    return (
      <Div className="col-md-9">
        <div className="panel card">
          <div className="panel-heading card-header">{translations.products}</div>
          <div className="card-body">
            {/*todo: move to order warning component*/}
            {order.details.vouchers &&
            <>
              <div className="alert alert-warning" role="alert">
                {
                  translations.refundWarning.replace(
                    '%1s',
                    formatCurrency(
                      parseFloat(order.availableRefundAmount.value),
                      get(currencies, order.availableRefundAmount.currency)
                    )
                  )
                }
              </div>
            </>
            }
            {!order || (!order.lines.length && <EmptyOrderLinesTable/>)}
            {!!order && !!order.lines.length && <OrderLinesTable/>}
          </div>
        </div>
      </Div>
    );
}
