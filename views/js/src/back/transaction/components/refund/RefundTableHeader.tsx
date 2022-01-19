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
import { useMappedState } from 'redux-react-hook';

export default function RefundTableHeader(): ReactElement<{}> {
  const { translations }: Partial<IMollieOrderState> = useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
  }));

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
