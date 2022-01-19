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
import { StoreContext } from 'redux-react-hook';
import { render } from 'react-dom';

import { IMollieCarrierConfig, ITranslations } from '@shared/globals';

export default (
  target: string,
  config: IMollieCarrierConfig,
  translations: ITranslations
) => {
  (async function() {
    const [
      { default: store },
      { updateConfig, updateTranslations },
      { default: CarrierConfig },
    ] = await Promise.all([
      import(/* webpackChunkName: "carrierconfig" */ '@carrierconfig/store'),
      import(/* webpackChunkName: "carrierconfig" */ '@carrierconfig/store/actions'),
      import(/* webpackChunkName: "carrierconfig" */ '@carrierconfig/components/CarrierConfig'),
    ]);

    store.dispatch(updateConfig(config));
    store.dispatch(updateTranslations(translations));

    return render(
      (
        <StoreContext.Provider value={store}>
          <CarrierConfig translations={translations} config={config} target={target}/>
        </StoreContext.Provider>
      ),
      document.getElementById(`${target}_container`)
    );
  }());
};
