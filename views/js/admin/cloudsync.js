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

// Load Tailwind CSS CDN
(function() {
  var tailwindScript = document.createElement('script');
  tailwindScript.src = 'https://cdn.tailwindcss.com';
  document.head.appendChild(tailwindScript);
  console.log('Tailwind CSS CDN loaded');
})();

// Wait 0.5 seconds and reload globals.css to ensure it's loaded last
setTimeout(function() {
  var existingLink = document.querySelector('link[href*="globals.css"]');
  if (existingLink) {
    existingLink.remove();
  }

  // Create new link element and append to head (loaded last)
  var newLink = document.createElement('link');
  newLink.rel = 'stylesheet';
  // Use module version for cache busting if available, otherwise fall back to timestamp
  var moduleVersion = (typeof window !== 'undefined' && window.mollieModuleVersion) ? window.mollieModuleVersion : new Date().getTime();
  newLink.href = '../modules/mollie/views/js/admin/library/dist/assets/globals.css?v=' + moduleVersion;
  document.head.appendChild(newLink);
}, 500);

(function($) {
  if (typeof $ === 'undefined') {
    return;
  }

  $(document).ready(function () {
    window?.psaccountsVue?.init();

    const cdc = window.cloudSyncSharingConsent;

    cdc.init('#prestashop-cloudsync');
    cdc.on('OnboardingCompleted', (isCompleted) => {
      console.log('OnboardingCompleted', isCompleted);

    });
    cdc.isOnboardingCompleted((isCompleted) => {
      console.log('Onboarding is already Completed', isCompleted);
    });
  });
})(window.jQuery);
