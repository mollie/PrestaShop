/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import React from 'react';
import { render } from 'react-dom';

import { IMollieMethodConfig, ITranslations } from '@shared/globals';

export default (target: string, config: IMollieMethodConfig, translations: ITranslations): void => {
  (async function () {
    const [
      { default: PaymentMethodConfig },
    ] = await Promise.all([
      import(/* webpackPrefetch: true, webpackChunkName: "methodconfig" */ '@methodconfig/components/PaymentMethodConfig'),
    ]);

    render(<PaymentMethodConfig target={target} config={config} translations={translations}/>, document.getElementById(`${target}_container`));
  }());
};
