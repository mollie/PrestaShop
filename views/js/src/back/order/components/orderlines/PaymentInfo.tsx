/**
 * Copyright (c) 2012-2018, Mollie B.V.
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
import React, { Component } from 'react';
import { connect } from 'react-redux';
import moment from 'moment';
import _ from 'lodash';
import styled from 'styled-components';

import { formatCurrency } from '../../../misc/tools';

interface IProps {
  // Redux
  order?: IMollieApiOrder,
  translations?: ITranslations,
  currencies?: ICurrencies,
}

const Div = styled.div`
@media only screen and (min-width: 992px) {
  margin-left: -5px!important;
  margin-right: 5px!important;
}
` as any;

class PaymentInfo extends Component<IProps> {
  render() {
    const { translations, order, currencies } = this.props;

    return (
      <Div className="col-md-3 panel">
        <div className="panel-heading">{translations.paymentInfo}</div>
        <h4>{translations.orderInfo}</h4>
        <div><strong>{translations.transactionId}</strong>: <span>{order.id}</span></div>
        <div><strong>{translations.date}</strong>: <span>{moment(order.createdAt).format('YYYY-MM-DD HH:mm:ss')}</span></div>
        <div><strong>{translations.amount}</strong>: <span>{formatCurrency(parseFloat(order.amount.value), _.get(currencies, order.amount.currency))}</span></div>
        {/*<div><strong>{translations.refunded}</strong>: <span>{formatCurrency(parseFloat(order.amountRefunded.value), _.get(currencies, order.amountRefunded.currency))}</span></div>*/}
        {/*<div><strong style={{ textDecoration: 'underline' }}>{translations.currentAmount}</strong>: <span>{formatCurrency(parseFloat(order.settlementAmount.value) - parseFloat(order.amountRefunded.value), _.get(currencies, order.settlementAmount.currency))}</span></div>*/}
      </Div>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
    order: state.order,
    currencies: state.currencies,
  })
)(PaymentInfo);
