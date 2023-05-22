/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */
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
    'input[name^="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_INCL_"],' +
    'input[name^="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_EXCL_"]',
    function () {
      clearTimeout(typingTimer);

      const inputValue = this.value;
      const inputElement = this;

      if (inputValue) {
        typingTimer = setTimeout(async function () {
          let inputName = $(inputElement).attr('name');

          let paymentFeeTaxIncl = 0.00;
          let paymentFeeTaxExcl = 0.00;

          if (inputName.indexOf('MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_INCL_') >= 0) {
            paymentFeeTaxIncl = inputValue;
          } else {
            paymentFeeTaxExcl = inputValue;
          }

          const $paymentMethod = $(inputElement.closest('div[id^="payment-method-form-"]'));

          if ($paymentMethod.length < 1) {
            console.error('Failed to find payment form parent element');

            return;
          }

          let taxRuleId = $paymentMethod.find('select[name^="MOLLIE_METHOD_TAX_RULE_ID_"]').val();

          updatePaymentFee($paymentMethod, paymentFeeTaxIncl, paymentFeeTaxExcl, taxRuleId);
        }, doneTypingInterval)
      }
    });

  $(document).on('change',
    'select[name^="MOLLIE_METHOD_TAX_RULE_ID_"]',
    function () {
      const taxRuleId = this.value;
      const inputElement = this;

      const $paymentMethod = $(inputElement.closest('div[id^="payment-method-form-"]'));

      if ($paymentMethod.length < 1) {
        console.error('Failed to find payment form parent element');

        return;
      }

      let paymentFeeTaxIncl = 0.00;
      let $paymentFeeTaxExcl = $paymentMethod.find('input[name^="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_EXCL_"]');

      if ($paymentFeeTaxExcl.length < 1) {
        console.error('Failed to find payment fee tax excluded price');

        return;
      }

      let paymentFeeTaxExcl = $paymentFeeTaxExcl.val();

      updatePaymentFee($paymentMethod, paymentFeeTaxIncl, paymentFeeTaxExcl, taxRuleId);
    });

  function updatePaymentFee($paymentMethod, paymentFeeTaxIncl, paymentFeeTaxExcl, taxRuleId) {
    $.ajax(ajaxUrl, {
        method: 'POST',
        data: {
          'action': 'updateFixedPaymentFeePrice',
          'paymentFeeTaxIncl': paymentFeeTaxIncl,
          'paymentFeeTaxExcl': paymentFeeTaxExcl,
          'taxRuleId': taxRuleId,
          'ajax': 1,
        },
        success: function (response) {
          response = JSON.parse(response);

          if (response.error) {
            console.error(response.message)

            return;
          }

          let $paymentFeeTaxIncl = $paymentMethod.find('input[name^="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_INCL_"]');
          let $paymentFeeTaxExcl = $paymentMethod.find('input[name^="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_EXCL_"]');

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
