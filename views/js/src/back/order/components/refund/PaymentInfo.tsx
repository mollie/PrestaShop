import React, { Component } from 'react';
import { connect } from 'react-redux';
import moment from 'moment';
import _ from 'lodash';
import styled from 'styled-components';

import { formatCurrency } from '../../misc/tools';

interface IProps {
  // Redux
  payment?: IMollieApiPayment,
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
    const { translations, payment, currencies } = this.props;

    return (
      <Div className="col-md-6 panel">
        <div className="panel-heading">{translations.payments}</div>
        <h4>{translations.paymentInfo}</h4>
        <div><strong>{translations.transactionId}</strong>: <span>{payment.id}</span></div>
        <div><strong>{translations.date}</strong>: <span>{moment(payment.createdAt).format('YYYY-MM-DD HH:mm:ss')}</span></div>
        <div><strong>{translations.amount}</strong>: <span>{formatCurrency(parseFloat(payment.settlementAmount.value), _.get(currencies, payment.settlementAmount.currency))}</span></div>
        <div><strong>{translations.refunded}</strong>: <span>{formatCurrency(parseFloat(payment.amountRefunded.value), _.get(currencies, payment.amountRefunded.currency))}</span></div>
        <div><strong style={{ textDecoration: 'underline' }}>{translations.currentAmount}</strong>: <span>{formatCurrency(parseFloat(payment.settlementAmount.value) - parseFloat(payment.amountRefunded.value), _.get(currencies, payment.settlementAmount.currency))}</span></div>
      </Div>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
    payment: state.payment,
    currencies: state.currencies,
  })
)(PaymentInfo);
