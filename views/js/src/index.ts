/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
export default {
  async bankList() {
    return await import(/* webpackChunkName: "banks" */ '@banks/index');
  },
  async qrCode() {
    return await import(/* webpackChunkName: "qrcode" */ '@qrcode/index');
  },
  async carrierConfig() {
    return await import(/* webpackChunkName: "carrierconfig" */ '@carrierconfig/index');
  },
  async methodConfig() {
    return await import(/* webpackChunkName: "methodconfig" */ '@methodconfig/index');
  },
  async transactionInfo() {
    return await import(/* webpackChunkName: "transaction" */ '@transaction/index');
  },
  async updater() {
    return await import(/* webpackChunkName: "updater" */ '@updater/index');
  },
}
