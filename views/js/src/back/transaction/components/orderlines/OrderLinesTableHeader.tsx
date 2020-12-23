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

import { ITranslations } from '@shared/globals';
import { useMappedState } from 'redux-react-hook';

interface IProps {
  // Redux
  translations?: ITranslations;
  viewportWidth?: number;
}

export default function OrderLinesTableHeader(): ReactElement<{}> {
  const { translations, viewportWidth }: Partial<IMollieOrderState> = useCallback(useMappedState((state: IMollieOrderState): any => ({
    translations: state.translations,
    viewportWidth: state.viewportWidth,
  })), []);

  return (
    <thead>
      <tr>
        <th>
          <span className="title_box"><strong>{translations.product}</strong></span>
        </th>
        <th>
          <span className="title_box">{translations.status}</span>
        </th>
        {viewportWidth < 1390 && (
          <th>
            <span className="title_box">
              <span>{translations.shipped}</span>
              <br/> <span style={{ whiteSpace: 'nowrap' }}>/ {translations.canceled}</span>
              <br/> <span style={{ whiteSpace: 'nowrap' }}>/ {translations.refunded}</span>
            </span>
          </th>
        )}
        {viewportWidth >= 1390 && (
          <>
            <th>
              <span className="title_box">{translations.shipped}</span>
            </th>
            <th>
              <span className="title_box">{translations.canceled}</span>
            </th>
            <th>
              <span className="title_box">{translations.refunded}</span>
            </th>
          </>
        )}
        <th>
          <span className="title_box">{translations.unitPrice}</span>
        </th>
        <th>
          <span className="title_box">{translations.vatAmount}</span>
        </th>
        <th>
          <span className="title_box">{translations.totalAmount}</span>
        </th>
        <th/>
      </tr>
    </thead>
  );
}
