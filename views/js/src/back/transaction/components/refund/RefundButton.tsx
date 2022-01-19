/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React, { ReactElement, useCallback } from 'react';
import cx from 'classnames';
import { useMappedState } from 'redux-react-hook';

interface IProps {
  loading: boolean;
  disabled: boolean;
  refundPayment: any;
}

export default function RefundButton({ loading, disabled, refundPayment }: IProps): ReactElement<{}> {
  const { translations,  }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
  }));

  return (
    <button
      type="button"
      className="btn btn-default"
      disabled={loading || disabled}
      onClick={() => refundPayment(false)}
      style={{ marginRight: '10px' }}
    >
      <i className={cx({
        'icon': true,
        'icon-undo': !loading,
        'icon-circle-o-notch': loading,
        'icon-spin': loading,
      })}/> {translations.refundOrder}
    </button>
  );
}
