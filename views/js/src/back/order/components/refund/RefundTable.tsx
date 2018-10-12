import React, { Component } from 'react';
import RefundTableHeader from './RefundTableHeader';
import { connect } from 'react-redux';
import moment from 'moment';
import _ from 'lodash';
import { formatCurrency } from '../../misc/tools';

interface IProps {
  // Redux
  payment?: IMollieApiPayment,
  currencies?: ICurrencies,
}

class RefundTable extends Component<IProps> {
  render() {
    const { payment, currencies } = this.props;

    return (
      <table className="table">
        <RefundTableHeader/>
        <tbody>
          {payment.refunds.map((refund: IMollieApiRefund) => (
            <tr key={refund.id} style={{ marginBottom: '100px' }}>
              <td style={{ width: '100px' }}><strong>{refund.id}</strong></td>
              <td>{moment(refund.createdAt).format('YYYY-MM-DD HH:mm:ss')}</td>
              <td>{formatCurrency(parseFloat(refund.amount.value), _.get(currencies, refund.amount.currency))}</td>
            </tr>
          ))}
        </tbody>
      </table>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    payment: state.payment,
    currencies: state.currencies,
  })
)(RefundTable);
