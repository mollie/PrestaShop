import React, { Component } from 'react';
import { connect } from 'react-redux';
import RefundTableHeader from './RefundTableHeader';

interface IProps {
  // Redux
  translations?: ITranslations,
}

class EmptyRefundTable extends Component<IProps> {
  render() {
    const { translations } = this.props;

    return (
      <table className="table">
        <RefundTableHeader/>
        <tbody>
          <tr>
            <td className="list-empty hidden-print" colSpan={3}>
              <div className="list-empty-msg">
                <i className="icon-warning-sign list-empty-icon"/>
                {translations.thereAreNoRefunds}
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    )
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations
  })
)(EmptyRefundTable);
