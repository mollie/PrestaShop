import React, { Component } from 'react';
import { connect } from 'react-redux';

interface IProps {
  // Redux
  translations?: ITranslations,
}

class RefundTableHeader extends Component<IProps> {
  render() {
    const { translations } = this.props;

    return (
      <thead>
        <tr>
          <th>
            <span className="title_box"><strong>{translations.ID}</strong></span>
          </th>
          <th>
            <span className="title_box">{translations.date}</span>
          </th>
          <th>
            <span className="title_box">{translations.amount}</span>
          </th>
        </tr>
      </thead>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
  })
)(RefundTableHeader);
