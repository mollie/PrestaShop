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

import OrderLinesTableHeader from '@transaction/components/orderlines/OrderLinesTableHeader';
import { IMollieOrderConfig, ITranslations } from '@shared/globals';
import { useMappedState } from 'redux-react-hook';

interface IProps {
  // Redux
  translations?: ITranslations;
  config?: IMollieOrderConfig;
}

export default function EmptyOrderLinesTable(): ReactElement<{}> {
  const { translations, config: { legacy } }: IProps = useCallback(useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
    config: state.config,
  })), []);

  if (legacy) {
    return <div className="error">{translations.thereAreNoProducts}</div>;
  }

  return (
    <div className="table-responsive">
      <table className="table">
        <OrderLinesTableHeader/>
        <tbody>
          <tr>
            <td className="list-empty hidden-print" colSpan={3}>
              <div className="list-empty-msg">
                <i className="icon-warning-sign list-empty-icon"/>
                {translations.thereAreNoProducts}
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  );
}
