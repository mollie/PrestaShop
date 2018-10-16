import React, { Component, Fragment } from 'react';
import { connect } from 'react-redux';
import moment from 'moment';
import { formatCurrency } from '../../../misc/tools';
import _ from 'lodash';

interface IProps {
  // Redux
  translations?: ITranslations,
  currencies?: ICurrencies,
  order?: IMollieApiOrder,
  config?: IMollieOrderConfig,
}

class PaymentInfoContent extends Component<IProps> {
  render() {
    const { translations, order, currencies, config: { legacy } } = this.props;

    return (
      <Fragment>
        {legacy && <h3>{translations.transactionInfo}</h3>}
        {!legacy && <h4>{translations.transactionInfo}</h4>}
        <strong>{translations.transactionId}</strong>: <span>{order.id}</span><br/>
        <strong>{translations.date}</strong>: <span>{moment(order.createdAt).format('YYYY-MM-DD HH:mm:ss')}</span><br/>
        <strong>{translations.amount}</strong>: <span>{formatCurrency(parseFloat(order.amount.value), _.get(currencies, order.amount.currency))}</span><br/>
        {/*<div><strong>{translations.refunded}</strong>: <span>{formatCurrency(parseFloat(order.amountRefunded.value), _.get(currencies, order.amountRefunded.currency))}</span></div>*/}
        {/*<div><strong style={{ textDecoration: 'underline' }}>{translations.currentAmount}</strong>: <span>{formatCurrency(parseFloat(order.settlementAmount.value) - parseFloat(order.amountRefunded.value), _.get(currencies, order.settlementAmount.currency))}</span></div>*/}
      </Fragment>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    order: state.order,
    currencies: state.currencies,
    translations: state.translations,
    config: state.config,
  })
)(PaymentInfoContent);
