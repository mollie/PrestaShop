import React, { Component, Fragment } from 'react';
import { connect } from 'react-redux';
import moment from 'moment';
import _ from 'lodash';

import { formatCurrency } from '../../misc/tools';

interface IProps {
  // Redux
  payment?: IMollieApiPayment,
  translations?: ITranslations,
  currencies?: ICurrencies,
}

class PaymentInfo extends Component<IProps> {
  render() {
    const { translations, payment, currencies } = this.props;

    return (
      <Fragment>
        <h4>{translations.paymentInfo}</h4>
        <div><strong>{translations.transactionId}</strong>: <span>{payment.id}</span></div>
        <div><strong>{translations.date}</strong>: <span>{moment(payment.createdAt).format('YYYY-MM-DD HH:mm:ss')}</span></div>
        <div><strong>{translations.amount}</strong>: <span>{formatCurrency(parseFloat(payment.settlementAmount.value), _.get(currencies, payment.settlementAmount.currency))}</span></div>
      </Fragment>
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
