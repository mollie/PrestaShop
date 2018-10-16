import React, { Component, Fragment } from 'react';
import { connect } from 'react-redux';
import classnames from 'classnames';

import LoadingDots from '../../../misc/components/LoadingDots';
import PaymentInfo from './PaymentInfo';
import OrderLinesInfo from './OrderLinesInfo';

interface IProps {
  // Redux
  order?: IMollieApiOrder,
  config?: IMollieOrderConfig,
}

class OrderPanelContent extends Component<IProps> {
  render() {
    const { order, config: { legacy } } = this.props;

    return (
      <Fragment>
        {!order && <Fragment><LoadingDots/></Fragment>}
        {!!order && order.status && (
          <div className={
            classnames({
              'panel-body': !legacy,
              'row': !legacy,
            })}
          >
            <PaymentInfo/>
            <OrderLinesInfo/>
          </div>
        )}
      </Fragment>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    order: state.order,
    config: state.config,
  })
)(OrderPanelContent);
