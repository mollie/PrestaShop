import React, { Component, Fragment } from 'react';
import { connect } from 'react-redux';
import EmptyRefundTable from './EmptyRefundTable';
import RefundTable from './RefundTable';

interface IProps {
  // Redux
  payment?: IMollieApiPayment,
  translations?: ITranslations,
}

class RefundHistory extends Component<IProps> {
  render() {
    const { translations, payment } = this.props;

    return (
      <Fragment>
        <h4>{translations.refundHistory}</h4>
        {!payment.refunds.length && <EmptyRefundTable/>}
        {!!payment.refunds.length && <RefundTable/>}
      </Fragment>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
    payment: state.payment,
  })
)(RefundHistory);
