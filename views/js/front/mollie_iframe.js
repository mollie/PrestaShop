/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

$(document).ready(function () {
    var $mollieContainers = $('.mollie-iframe-container');
    if (!$mollieContainers.length) {
        return;
    }

    var overridePrestaShopsAdditionalInformationHideFunctionality = function ($mollieContainer) {
      var $additionalInformationContainer = $mollieContainer.closest('.additional-information');

      // this allows for us to have our custom hide functionality
      $additionalInformationContainer.addClass('mollie-credit-card-container__hide')

      // making the container visible
      $additionalInformationContainer.css('display', 'block')
      // removing any additional classes that might also set element to be hidden
      $additionalInformationContainer.removeClass('ps-hidden')
    }

    var showAdditionalInformation = function ($additionalInformation) {
      $additionalInformation.removeClass('mollie-credit-card-container__hide')
      $additionalInformation.addClass('mollie-credit-card-container__show')
    }

    var hideAdditionalInformation = function ($additionalInformation) {
      $additionalInformation.addClass('mollie-credit-card-container__hide')
      $additionalInformation.removeClass('mollie-credit-card-container__show')
    }

    overridePrestaShopsAdditionalInformationHideFunctionality($mollieContainers)

    // if credit card is somehow preselected its hidden content will be displayed
    var isMollieCreditCardPreselected = function ($iframeContainer) {
      var $additionalInformation = $iframeContainer.closest('.additional-information')
      var id = $additionalInformation.attr('id')

      var paymentOptionPrefix = id.replace('-additional-information', '')
      var $paymentOption = $('#' + paymentOptionPrefix)

      return $paymentOption.is(':checked')
    }

    if (isMollieCreditCardPreselected($mollieContainers)) {
      showAdditionalInformation($mollieContainers.closest('.additional-information'))
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
    var mollie = Mollie(profileId, {locale: isoCode, testMode: isTestMode});
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
    var methodId = $(this).find('input[name="method-id"]').val();
    mountMollieComponents(methodId);

    $(document).on('change', 'input[data-module-name="mollie"]', function () {
        var paymentOption = $(this).attr('id');
        var $additionalInformation = $('#' + paymentOption + '-additional-information');
        $additionalInformation.addClass('mollie-addition-info');

        showAdditionalInformation($additionalInformation)

        var methodId = $additionalInformation.find('input[name="method-id"]').val();
        if (!methodId) {
            return;
        }
        cardHolderInput.unmount();
        carNumberInput.unmount();
        expiryDateInput.unmount();
        verificationCodeInput.unmount();
        fieldErrors = {};
        handleErrors();
        $('.mollie-input').removeClass('is-invalid');
        mountMollieComponents(methodId);
    });

    $(document).on('change', 'input[name="payment-option"]', function () {
      var isMollie = $(this).attr('data-module-name') === 'mollie'

      if (isMollie) {
        return;
      }

      var $additionalInformation = $mollieContainers.closest('.additional-information')

      hideAdditionalInformation($additionalInformation)
    })

    function mountMollieComponents(methodId) {
        cardHolderInput = mountMollieField(this, '#card-holder', methodId, cardHolder, 'card-holder');
        carNumberInput = mountMollieField(this, '#card-number', methodId, cardNumber, 'card-number');
        expiryDateInput = mountMollieField(this, '#expiry-date', methodId, expiryDate, 'expiry-date');
        verificationCodeInput = mountMollieField(this, '#verification-code', methodId, verificationCode, 'verification-code');

        var $mollieCardToken = $('input[name="mollieCardToken' + methodId + '"]');
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
                $form[0].submit();
            });
        });
    }

    function mountMollieField(mollieContainer, holderId, methodId, inputHolder, methodName) {
        var invalidClass = 'is-invalid';
        var cardHolderId = holderId + '-' + methodId;
        inputHolder.mount(cardHolderId);
        inputHolder.addEventListener('change', function (event) {
            if (event.error && event.touched) {
                $(cardHolderId).addClass(invalidClass);
                fieldErrors[fieldMap[methodName]] = event.error;
                handleErrors();
            } else {
                fieldErrors[fieldMap[methodName]] = '';
                $(cardHolderId).removeClass(invalidClass);
                handleErrors();
            }
        });

        inputHolder.addEventListener("focus", function () {
            var $formGroup =   $('.form-group-' + methodName + '.' + methodId)

            var $additionalInformation = $formGroup.closest('.additional-information')

            if ($additionalInformation.hasClass('mollie-credit-card-container__hide')) {
              // if mollie is hidden do nothing with focus
              return
            }

            $formGroup.toggleClass('is-focused', true);
        });

        inputHolder.addEventListener("blur", function () {
            $('.form-group-' + methodName + '.' + methodId).toggleClass('is-focused', false);
        });
        inputHolder.addEventListener("change", function (event) {
            $('.form-group-' + methodName + '.' + methodId).toggleClass('is-dirty', event.dirty);
        });
        return inputHolder;
    }

    function handleErrors() {
        var $errorField = $('#mollie-field-error');
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
