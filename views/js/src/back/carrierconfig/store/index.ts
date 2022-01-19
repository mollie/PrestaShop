/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import { createStore, Store } from 'redux';

import carriersApp from '@carrierconfig/store/carriers';

declare let window: any;

let store: Store;
const devTools = window.__REDUX_DEVTOOLS_EXTENSION__;

store = createStore(
  carriersApp,
  devTools && devTools(),
);

export default store;
