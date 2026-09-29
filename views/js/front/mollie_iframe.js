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
    var $mollieContainers = $();

    // Hummingbird renames the wrapper, and a custom theme may carry only the plain class.
    var additionalInformationSelector = '.js-additional-information, .additional-information';

    var overridePrestaShopsAdditionalInformationHideFunctionality = function ($mollieContainer) {
      var $additionalInformationContainer = $mollieContainer.closest(additionalInformationSelector);

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

    // if credit card is somehow preselected its hidden content will be displayed
    var isMollieCreditCardPreselected = function ($iframeContainer) {
      var $additionalInformation = $iframeContainer.closest(additionalInformationSelector)
      var id = $additionalInformation.attr('id')

      if (!id) {
        return false
      }

      var paymentOptionPrefix = id.replace('-additional-information', '')
      var $paymentOption = $('#' + paymentOptionPrefix)

      return $paymentOption.is(':checked')
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
    var mollie;
    var cardHolder;
    var cardNumber;
    var expiryDate;
    var verificationCode;

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
    var isCreatingToken = false;

    initMollieCardFields();

    // The one-page checkout renders the payment list after the page has loaded and replaces it
    // on every address, carrier or cart change, so the fields have to be mounted again each time.
    if (window.prestashop && typeof window.prestashop.on === 'function') {
        $.each(['opcPaymentMethodsUpdated', 'opcPaymentMethodsRefreshed'], function (index, eventName) {
            window.prestashop.on(eventName, function () {
                initMollieCardFields();
            });
        });
    }

    function initMollieCardFields() {
        $mollieContainers = $('.mollie-iframe-container');
        if (!$mollieContainers.length) {
            return;
        }

        // A refresh that left the fields in place must not remount them - that would drop
        // whatever the shopper has already typed into the card inputs.
        if (hasMountedCardFields()) {
            return;
        }

        overridePrestaShopsAdditionalInformationHideFunctionality($mollieContainers)

        if (isMollieCreditCardPreselected($mollieContainers)) {
          showAdditionalInformation($mollieContainers.closest(additionalInformationSelector))
        }

        // createToken() requires every component ever created on the instance to be mounted, so
        // the same four are reused and remounted rather than created again per rebuild.
        if (!mollie) {
            mollie = Mollie(profileId, {locale: isoCode, testMode: isTestMode});
            cardHolder = mollie.createComponent('cardHolder', options);
            cardNumber = mollie.createComponent('cardNumber', options);
            expiryDate = mollie.createComponent('expiryDate', options);
            verificationCode = mollie.createComponent('verificationCode', options);
        } else {
            unmountMollieComponents();
        }

        fieldErrors = {};
        mountMollieComponents();
    }

    $(document).on('change', 'input[data-module-name="mollie"]', function () {
        var paymentOption = $(this).attr('id');
        var $additionalInformation = $('#' + paymentOption + '-additional-information');
        $additionalInformation.addClass('mollie-addition-info');

        showAdditionalInformation($additionalInformation)

        var methodId = $additionalInformation.find('input[name="mollie-method-id"]').val();
        // Remounting fields that are already live races the component handshake and can leave
        // one of them blank, so only rebuild them when they are actually missing.
        if (methodId !== 'creditcard' || !mollie || hasMountedCardFields()) {
            return;
        }
        unmountMollieComponents();
        fieldErrors = {};
        handleErrors();
        $('.mollie-input').removeClass('is-invalid');
        mountMollieComponents();
    });

    $(document).on('change', 'input[name="payment-option"]', function () {
      var isMollie = $(this).attr('data-module-name') === 'mollie'

      if (isMollie) {
        return;
      }

      var $additionalInformation = $mollieContainers.closest(additionalInformationSelector)

      hideAdditionalInformation($additionalInformation)
    })

    function mountMollieComponents() {
        methodId = 'creditcard';
        cardHolderInput = mountMollieField(this, '#card-holder', methodId, cardHolder, 'card-holder');
        carNumberInput = mountMollieField(this, '#card-number', methodId, cardNumber, 'card-number');
        expiryDateInput = mountMollieField(this, '#expiry-date', methodId, expiryDate, 'expiry-date');
        verificationCodeInput = mountMollieField(this, '#verification-code', methodId, verificationCode, 'verification-code');

        var $mollieCardToken = $('input[name="mollieCardToken"]');
        var $paymentForm = $mollieCardToken.closest('form');

        // A refreshed payment list can hand back the same form node, so only bind it once.
        if ($paymentForm.data('mollieSubmitBound')) {
            return;
        }
        $paymentForm.data('mollieSubmitBound', true);

        var isResubmit = false;
        $paymentForm.on('submit', function (event) {
            var $form = $(this);
            var useSavedCardCheckbox = $('input[name="mollie-use-saved-card"]');
            if (isResubmit || useSavedCardCheckbox.is(':checked')) {
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
                return;
            });

            $('#payment-confirmation').find('button[type=submit]').prop("disabled", false);
        });
    }

    function hasMountedCardFields() {
        var $fields = $('.mollie-input');

        return $fields.length > 0 && $fields.find('iframe').length >= $fields.length;
    }

    function unmountMollieComponents() {
        $.each([cardHolder, cardNumber, expiryDate, verificationCode], function (index, component) {
            if (!component) {
                return;
            }
            try {
                component.unmount();
            } catch (error) {
                // the field it was mounted into is already gone
            }
        });
    }

    function isMollieCreditCardSelected() {
        var $selected = $('input[name="payment-option"][data-module-name="mollie"]:checked');
        if (!$selected.length) {
            return false;
        }

        var $additionalInformation = $('#' + $selected.attr('id') + '-additional-information');

        return $additionalInformation.find('input[name="mollie-method-id"]').val() === 'creditcard';
    }

    // The one-page checkout submits the payment form through HTMLFormElement.prototype.submit(),
    // which fires no submit event, so the token has to be created before its own handler runs.
    // Capturing on the document guarantees that; the click is replayed once the token is in.
    document.addEventListener('click', function (event) {
        var $payButton = $(event.target).closest('#opc-pay-button');

        if (!$payButton.length || !mollie || isCreatingToken) {
            return;
        }

        if (!isMollieCreditCardSelected() || $('input[name="mollie-use-saved-card"]').is(':checked')) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        mollie.createToken().then(function (token) {
            if (token.error) {
                var $mollieAlert = $('.js-mollie-alert');
                $mollieAlert.closest('article').show();
                $mollieAlert.text(token.error.message);
                return;
            }

            $('input[name="mollieCardToken"]').val(token.token);

            isCreatingToken = true;
            $payButton[0].click();
            isCreatingToken = false;
        });
    }, true);

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

            var $additionalInformation = $formGroup.closest(additionalInformationSelector)

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
