$(document).ready(function () {
    function initMollieAccount() {
        var source = $('input[name="MOLLIE_ACCOUNT_SWITCH"]');

        function checkInput(e) {
            if ($('input[name="MOLLIE_ACCOUNT_SWITCH"]:checked').val() == '0') {
                e.closest('.form-group')
                    .next('.form-group').hide()
                    .next('.form-group').hide()
                    .next('.form-group').hide();
                e.closest('.form-group')
                    .find('.help-block').show()
                    .find('.help-block').show()
                    .find('.help-block').show();
            } else {
                e.closest('.form-group')
                    .next('.form-group').show()
                    .next('.form-group').show()
                    .next('.form-group').show();
                e.closest('.form-group')
                    .find('.help-block').hide()
                    .find('.help-block').hide()
                    .find('.help-block').hide();
            }
        }

        setTimeout(function () {
            checkInput(source);
        }, 100);

        $(source).on('change', function () {
            checkInput(source);
        });
    }

    initMollieAccount();
});