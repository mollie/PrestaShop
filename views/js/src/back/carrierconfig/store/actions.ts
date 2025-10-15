/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
// Action types
import { IMollieCarrierConfig, ITranslations } from '@shared/globals';

export enum ReduxActionTypes {
  updateTranslations = 'UPDATE_MOLLIE_CARRIER_TRANSLATIONS',
  updateConfig = 'UPDATE_MOLLIE_CARRIER_CONFIG',
}

// Action creators
export interface IUpdateTranslationsAction {
  type: string;
  translations: ITranslations;
}

export interface IUpdateCarrierConfigAction {
  type: string;
  config: IMollieCarrierConfig;
}

export function updateTranslations(translations: ITranslations): IUpdateTranslationsAction {
  return { type: ReduxActionTypes.updateTranslations, translations };
}

export function updateConfig(config: IMollieCarrierConfig): IUpdateCarrierConfigAction {
  return { type: ReduxActionTypes.updateConfig, config };
}
