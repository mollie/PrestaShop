{*
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 *}

<prestashop-accounts></prestashop-accounts>
<br>
<div id="prestashop-cloudsync"></div>

<div id="ps-modal"></div>

<br>

<script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}"></script>
<script src="{$urlCloudsync|escape:'htmlall':'UTF-8'}"></script>

<script>
    window?.psaccountsVue?.init();
    // CloudSync
    const cdc = window.cloudSyncSharingConsent;
    cdc.init('#prestashop-cloudsync');
    cdc.on('OnboardingCompleted', (isCompleted) => {
        console.log('OnboardingCompleted', isCompleted);
    });
    cdc.isOnboardingCompleted((isCompleted) => {
        console.log('Onboarding is already Completed', isCompleted);
    });
</script>
