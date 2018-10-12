import React, { Component } from 'react';
import { connect } from 'react-redux';

interface IProps {

}

class ShippingModalBody extends Component<IProps> {
  render() {
    return (
      <p>test</p>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
  })
)(ShippingModalBody);
