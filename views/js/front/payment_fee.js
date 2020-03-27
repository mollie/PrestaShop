$(document).ready(function () {
    displayPaymentFee();
    function displayPaymentFee() {
        var paymentFees = $('input[name="payment-fee-price"]');
        paymentFees.each(function () {
            var $prev = $(this).closest('.js-payment-option-form').prev();
            if ($prev.hasClass('additional-information')) {
                $prev.prev().find('label').append($(this).val());
            } else {
                $prev.find('label').append($(this).val());
            }
        });
    }
});