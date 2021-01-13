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

import RefundTableHeader from '@transaction/components/refund/RefundTableHeader';
import { useMappedState } from 'redux-react-hook';

export default function EmptyRefundTable(): ReactElement<{}> {
  const { translations }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations
  }));

  return (
    <div className="table-responsive">
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
    </div>
  );
}
