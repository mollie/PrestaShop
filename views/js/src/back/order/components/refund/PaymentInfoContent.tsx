import React, { Component, Fragment } from 'react';
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
      <Fragment>
        {legacy && <h3>{translations.transactionInfo}</h3>}
        {!legacy && <h4>{translations.transactionInfo}</h4>}
        <strong>{translations.transactionId}</strong>: <span>{payment.id}</span><br/>
        <strong>{translations.date}</strong>: <span>{moment(payment.createdAt).format('YYYY-MM-DD HH:mm:ss')}</span><br/>
        <strong>{translations.amount}</strong>: <span>{formatCurrency(parseFloat(payment.amount.value), _.get(currencies, payment.amount.currency))}</span><br/>
        {/*<div><strong>{translations.refunded}</strong>: <span>{formatCurrency(parseFloat(payment.amountRefunded.value), _.get(currencies, payment.amountRefunded.currency))}</span></div>*/}
        {/*<div><strong style={{ textDecoration: 'underline' }}>{translations.currentAmount}</strong>: <span>{formatCurrency(parseFloat(payment.settlementAmount.value) - parseFloat(payment.amountRefunded.value), _.get(currencies, payment.settlementAmount.currency))}</span></div>*/}
      </Fragment>
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
