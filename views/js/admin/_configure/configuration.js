$(document).ready(function () {
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
});
