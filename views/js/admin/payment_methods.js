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

$(document).ready(function() {
  // jquery sortable plugin must be included. @see https://jqueryui.com/sortable/
  var $sortableElement = $('#js-payment-methods-sortable')

  $sortableElement.sortable({
    appendTo: document.body,
    handle: '.js-sort-handle'
  });

  $sortableElement.bind( "sortupdate", function(event, ui) {
    $('.js-payment-option-position').each(function (index) {
      $(this).val(index)
    })
  });

  $('.payment-method .js-payment-option-position') .on('focus', function(e) {
    if (this.setSelectionRange) {
      var len = $(this).val().length;
      this.setSelectionRange(len, len);
    } else {
      $(this).val($(this).val());
    }

    $('.payment-method').attr("draggable", false); }) .on('blur', function(e) {
      $('.payment-method').attr("draggable", true);
    });

  $('input[name="activateModule"]').parent('div').hide();

  let typingTimer;
  let doneTypingInterval = 500;

  $(document).on('keyup',
    'input[name^="' + paymentMethodSurchargeFixedAmountTaxInclConfig + '"],' +
    'input[name^="' + paymentMethodSurchargeFixedAmountTaxExclConfig + '"]',
    function () {
      clearTimeout(typingTimer);

      const inputValue = this.value;
      const inputElement = this;

      if (inputValue) {
        typingTimer = setTimeout(function () {
          let inputName = $(inputElement).attr('name');

          let paymentFeeTaxIncl = 0.00;
          let paymentFeeTaxExcl = 0.00;

          if (inputName.indexOf(paymentMethodSurchargeFixedAmountTaxInclConfig) >= 0) {
            paymentFeeTaxIncl = inputValue;
          } else {
            paymentFeeTaxExcl = inputValue;
          }

          const $paymentMethod = $(inputElement.closest('div[id^="payment-method-form-"]'));

          if ($paymentMethod.length < 1) {
            console.error('Failed to find payment form parent element');

            return;
          }

          let taxRulesGroupId = $paymentMethod.find('select[name^="' + paymentMethodTaxRulesGroupIdConfig + '"]').val();

          updatePaymentFee($paymentMethod, paymentFeeTaxIncl, paymentFeeTaxExcl, taxRulesGroupId);
        }, doneTypingInterval)
      }
    });

  $(document).on('change',
    'select[name^="' + paymentMethodTaxRulesGroupIdConfig + '"]',
    function () {
      const taxRulesGroupId = this.value;
      const inputElement = this;

      const $paymentMethod = $(inputElement.closest('div[id^="payment-method-form-"]'));

      if ($paymentMethod.length < 1) {
        console.error('Failed to find payment form parent element');

        return;
      }

      let paymentFeeTaxIncl = 0.00;
      let $paymentFeeTaxExcl = $paymentMethod.find('input[name^="' + paymentMethodSurchargeFixedAmountTaxExclConfig + '"]');

      if ($paymentFeeTaxExcl.length < 1) {
        console.error('Failed to find payment fee tax excluded price');

        return;
      }

      let paymentFeeTaxExcl = $paymentFeeTaxExcl.val();

      updatePaymentFee($paymentMethod, paymentFeeTaxIncl, paymentFeeTaxExcl, taxRulesGroupId);
    });

  function updatePaymentFee($paymentMethod, paymentFeeTaxIncl, paymentFeeTaxExcl, taxRulesGroupId) {
    $.ajax(ajaxUrl, {
        method: 'POST',
        data: {
          'action': 'updateFixedPaymentFeePrice',
          'paymentFeeTaxIncl': paymentFeeTaxIncl,
          'paymentFeeTaxExcl': paymentFeeTaxExcl,
          'taxRulesGroupId': taxRulesGroupId,
          'ajax': 1,
        },
        success: function (response) {
          response = JSON.parse(response);

          if (response.error) {
            console.error(response.message)

            return;
          }

          let $paymentFeeTaxIncl = $paymentMethod.find('input[name^="' + paymentMethodSurchargeFixedAmountTaxInclConfig + '"]');
          let $paymentFeeTaxExcl = $paymentMethod.find('input[name^="' + paymentMethodSurchargeFixedAmountTaxExclConfig + '"]');

          if ($paymentFeeTaxIncl.length < 1 || $paymentFeeTaxExcl.length < 1) {
            console.error('Failed to find payment fee input');

            return;
          }

          $paymentFeeTaxIncl.val(response.paymentFeeTaxIncl);
          $paymentFeeTaxExcl.val(response.paymentFeeTaxExcl);
        }
      }
    )
  }
})
