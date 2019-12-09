$(document).ready(function () {
    $('.mollie_method.js_call_iframe').on('click', function () {
        event.preventDefault();
        $.fancybox({
            'padding': 0,
            'max-width': 200,
            'width' : 200,
            'height': 'auto',
            'fitToView': true,
            'autoSize': true,
            'type': 'inline',
            'content': $('#mollie-iframe-container').html()
        });
        mountMollieComponents();
    });

    var $mollieContainers = $('.mollie-iframe-container');
    if (!$mollieContainers.length) {
        return;
    }
    var options = {
        styles: {
            base: {
                color: "#222",
                fontSize: "15px;",
                padding: "15px"
            }
        }
    };
    var mollie = Mollie(profileId, {locale: isoCode, testMode: true});
    var cardHolder = mollie.createComponent('cardHolder', options);
    var cardNumber = mollie.createComponent('cardNumber', options);
    var expiryDate = mollie.createComponent('expiryDate', options);
    var verificationCode = mollie.createComponent('verificationCode', options);

    var cardHolderInput;
    var carNumberInput;
    var expiryDateInput;
    var verificationCodeInput;

    var fieldMap = {
        'card-holder': 0,
        'card-number': 1,
        'expiry-date': 2,
        'verification-code': 3
    };
    var fieldErrors = {};

    function mountMollieComponents() {
        cardHolderInput = mountMollieField(this, '.fancybox-outer #card-holder', cardHolder, 'card-holder');
        carNumberInput = mountMollieField(this, '.fancybox-outer #card-number', cardNumber, 'card-number');
        expiryDateInput = mountMollieField(this, '.fancybox-outer #expiry-date', expiryDate, 'expiry-date');
        verificationCodeInput = mountMollieField(this, '.fancybox-outer #verification-code', verificationCode, 'verification-code');

        var $mollieCardToken = $('input[name="mollieCardToken"]');
        var isResubmit = false;
        $mollieCardToken.closest('form').on('submit', function (event) {
            var $form = $(this);
            if (isResubmit) {
                return;
            }
            event.preventDefault();
            mollie.createToken().then(function (token) {
                if (token.error) {
                    var $mollieAlert = $('.js-mollie-alert');
                    $mollieAlert.closest('article').show();
                    $mollieAlert.text(token.error.message);
                    return;
                }
                $mollieCardToken.val(token.token);
                isResubmit = true;
                $form.submit();
            });
        });
    }

    function mountMollieField(mollieContainer, holderId, inputHolder, methodName) {
        var invalidClass = 'is-invalid';
        inputHolder.mount(holderId);
        inputHolder.addEventListener('change', function (event) {
            if (event.error && event.touched) {
                $(holderId).addClass(invalidClass);
                fieldErrors[fieldMap[methodName]] = event.error;
                handleErrors();
            } else {
                fieldErrors[fieldMap[methodName]] = '';
                $(holderId).removeClass(invalidClass);
                handleErrors();
            }
        });

        inputHolder.addEventListener("focus", function () {
            $('.form-group-' + methodName).toggleClass('is-focused', true);
        });

        inputHolder.addEventListener("blur", function () {
            $('.form-group-' + methodName).toggleClass('is-focused', false);
        });
        inputHolder.addEventListener("change", function (event) {
            $('.form-group-' + methodName).toggleClass('is-dirty', event.dirty);
        });
        return inputHolder;
    }

    function handleErrors() {
        var $errorField = $('.mollie-field-error');
        var hasError = 0;
        jQuery.each(fieldErrors, function (key, fieldError) {
            if (fieldError) {
                $errorField.find('label').text(fieldError);
                hasError = 1;
                return false;
            }
        });
        if (!hasError) {
            $errorField.find('label').text('');
        }
    }
});