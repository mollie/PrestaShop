import React, { Component } from 'react';
import { connect } from 'react-redux';
import classnames from 'classnames';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faTimes, faTruck, faUndo, faCircleNotch } from '@fortawesome/free-solid-svg-icons';

interface IProps {
  line: IMollieOrderLine,
  loading: boolean,
  shipLine: Function,
  cancelLine: Function,
  refundLine: Function,

  // Redux
  translations?: ITranslations,
  config?: IMollieOrderConfig,
}

class OrderLinesTableActions extends Component<IProps> {
  render() {
    const { config: { legacy }, translations, line, loading, shipLine, cancelLine, refundLine } = this.props;

    let shipButton = (
      <button
        style={{
          cursor: line.shippableQuantity < 1 ? 'not-allowed' : 'pointer',
          width: legacy ? '100px': 'auto',
          textAlign: legacy ? 'left': 'inherit',
          opacity: ((loading || line.refundableQuantity < 1) && legacy) ? 0.8 : 1,
        }}
        className={classnames({ 'btn': !legacy, 'btn-default': !legacy })}
        title=""
        disabled={loading || line.shippableQuantity < 1}
        onClick={() => shipLine([line])}
      >
        {legacy && <img
          src="../img/admin/delivery.gif"
          style={{
            filter: (loading || line.shippableQuantity < 1) ? 'grayscale(100%)' : null,
            WebkitFilter: (loading || line.shippableQuantity < 1) ? 'grayscale(100%)' : null,
          }}
        />}
        {!legacy && <FontAwesomeIcon icon={!loading ? faTruck : faCircleNotch} spin={loading}/>} {translations.ship}
      </button>
    );

    const refundButton = legacy ? (
      <button
        style={{
          cursor: line.refundableQuantity < 1 ? 'not-allowed' : 'pointer',
          width: '100px',
          textAlign: 'left',
          opacity: (loading || line.refundableQuantity < 1) ? 0.8 : 1,
        }}
        title=""
        disabled={loading || line.refundableQuantity < 1}
        onClick={() => refundLine([line])}
      >
        <img
          src="../img/admin/money.gif"
          style={{
            filter: (loading || line.refundableQuantity < 1) ? 'grayscale(100%)' : null,
            WebkitFilter: (loading || line.refundableQuantity < 1) ? 'grayscale(100%)' : null,
          }}
        /> {translations.refund}
      </button>
    ) : (
      <a
        style={{
          cursor: (loading || line.refundableQuantity < 1) ? 'not-allowed' : 'pointer',
          opacity: (loading || line.refundableQuantity < 1) ? 0.8 : 1,
        }}
        onClick={() => line.refundableQuantity > 0 && refundLine([line])}
      >
        <FontAwesomeIcon icon={!loading ? faUndo : faCircleNotch } spin={loading}/> {translations.refund}
      </a>
    );

    const cancelButton = legacy ? (
      <button
        style={{
          cursor: line.cancelableQuantity < 1 ? 'not-allowed' : 'pointer',
          width: '100px',
          textAlign: 'left',
          opacity: (loading || line.refundableQuantity < 1) ? 0.8 : 1,
        }}
        title=""
        disabled={loading || line.cancelableQuantity < 1}
        onClick={() => cancelLine([line])}
      >
        <img
          src="../img/admin/disabled.gif"
          style={{
            filter: (loading || line.cancelableQuantity < 1) ? 'grayscale(100%)' : null,
            WebkitFilter: (loading || line.cancelableQuantity < 1) ? 'grayscale(100%)' : null,
          }}
        /> {translations.cancel}
      </button>
    ) : (
      <a
        style={{
          cursor: (loading || line.cancelableQuantity < 1) ? 'not-allowed' : 'pointer',
          opacity: (loading || line.cancelableQuantity < 1) ? 0.8 : 1,
        }}
        onClick={() => line.cancelableQuantity > 0 && cancelLine([line])}
      >
        <FontAwesomeIcon icon={!loading ? faTimes : faCircleNotch} spin={loading}/> {translations.cancel}
      </a>
    );

    const buttonGroup = legacy ? (
      <div>
        {shipButton}
        {refundButton}
        {cancelButton}
      </div>
    ) : (
      <div className={classnames({
        'btn-group': !legacy,
      })}
      >
        {shipButton}
        <button
          type="button"
          className={classnames({
            'btn': !legacy,
            'btn-default': !legacy,
            'dropdown-toggle': !legacy,
          })}
          data-toggle={legacy ? 'dropdown' : null}
          disabled={loading || (line.refundableQuantity < 1 && line.cancelableQuantity < 1)}
        >
          <span className="caret">&nbsp;</span>
        </button>
        <ul className="dropdown-menu">
          <li>
            {refundButton}
          </li>
          <li>
            {cancelButton}
          </li>
        </ul>
      </div>
    );

    return (
      <div className={classnames({ 'btn-group-action': !legacy })}>
        {buttonGroup}
      </div>
    );
  }
}

export default connect<{}, {}, IProps>(
  (state: IMollieOrderState): Partial<IProps> => ({
    translations: state.translations,
    config: state.config,
  })
)(OrderLinesTableActions);
