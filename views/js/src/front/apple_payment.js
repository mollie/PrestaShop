$(() => {
    document.cookie = 'isApplePayMethod = 0';
    if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
        document.cookie = 'isApplePayMethod = 1';
    }
});