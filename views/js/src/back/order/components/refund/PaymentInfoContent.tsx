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

import { formatCurrency } from '../../../misc/tools';

interface IProps {
  // Redux
  translations?: ITranslations,
  currencies?: ICurrencies,
  payment?: IMollieApiPayment,
  config?: IMollieOrderConfig,
}

class PaymentInfoContent extends Component<IProps> {
  render() {
    const { translations, payment, currencies, config: { legacy } } = this.props;

    return (
      <>
        {legacy && <h3>{translations.transactionInfo}</h3>}
        {!legacy && <h4>{translations.transactionInfo}</h4>}
        <strong>{translations.transactionId}</strong>: <span>{payment.id}</span><br/>
        <strong>{translations.date}</strong>: <span>{moment(payment.createdAt).format('YYYY-MM-DD HH:mm:ss')}</span><br/>
        <strong>{translations.amount}</strong>: <span>{formatCurrency(parseFloat(payment.amount.value), _.get(currencies, payment.amount.currency))}</span><br/>
        {/*<div><strong>{translations.refunded}</strong>: <span>{formatCurrency(parseFloat(payment.amountRefunded.value), _.get(currencies, payment.amountRefunded.currency))}</span></div>*/}
        {/*<div><strong style={{ textDecoration: 'underline' }}>{translations.currentAmount}</strong>: <span>{formatCurrency(parseFloat(payment.settlementAmount.value) - parseFloat(payment.amountRefunded.value), _.get(currencies, payment.settlementAmount.currency))}</span></div>*/}
      </>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    payment: state.payment,
    currencies: state.currencies,
    translations: state.translations,
    config: state.config,
  })
)(PaymentInfoContent);
