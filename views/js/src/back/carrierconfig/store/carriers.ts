/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import { combineReducers } from 'redux';

import { IUpdateCarrierConfigAction, IUpdateTranslationsAction, ReduxActionTypes } from '@carrierconfig/store/actions';
import { IMollieCarrierConfig, IMollieCarrierConfigItem, ITranslations } from '@shared/globals';

declare global {
  interface IMollieCarriersState {
    translations: ITranslations;
    config: IMollieCarrierConfig;
    carriers: Array<IMollieCarrierConfigItem>;
  }
}

const translations = (state: any = {}, action: IUpdateTranslationsAction): ITranslations => {
  switch (action.type) {
    case ReduxActionTypes.updateTranslations:
      return action.translations;
    default:
      return state;
  }
};

const config = (state: any = {}, action: IUpdateCarrierConfigAction): IMollieCarrierConfig => {
  switch (action.type) {
    case ReduxActionTypes.updateConfig:
      return action.config;
    default:
      return state;
  }
};

const checkoutApp = combineReducers({
  translations,
  config,
});

export default checkoutApp;
