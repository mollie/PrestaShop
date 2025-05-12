/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */
$(document).ready(function () {
  window?.psaccountsVue?.init();

  // Cloud Sync
  const cdc = window.cloudSyncSharingConsent;

  cdc.init('#prestashop-cloudsync');
  cdc.on('OnboardingCompleted', (isCompleted) => {
    console.log('OnboardingCompleted', isCompleted);

  });
  cdc.isOnboardingCompleted((isCompleted) => {
    console.log('Onboarding is already Completed', isCompleted);
  });
});
