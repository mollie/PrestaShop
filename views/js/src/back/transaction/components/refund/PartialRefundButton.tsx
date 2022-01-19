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

export default function PartialRefundButton({ loading, disabled, refundPayment }: IProps): ReactElement<{}> {
  const { translations, config: { legacy } }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
    config: state.config,
  }));

  const content = (
    <button
      className="btn btn-default"
      type="button"
      disabled={loading || disabled}
      onClick={() => refundPayment(true)}
    >
      {!legacy && (<i
        className={cx({
          'icon': true,
          'icon-undo': !loading,
          'icon-circle-o-notch': loading,
          'icon-spin': loading,
        })}
      />)} {translations.partialRefund}
    </button>
  );

  if (legacy) {
    return content;
  }

  return (
    <div className="input-group-btn">
      {content}
    </div>
  );
}
