/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */
$(document).ready(function () {
    $('.js-mollie-amount').keypress(function (event) {
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });
    $('select[name^="MOLLIE_CARRIER_URL_SOURCE"]').on('change', function () {
        var customUrlDisabled = true;
        if ($(this).val() === 'custom_url') {
            customUrlDisabled = false;
        }
        $(this).closest('tr').find('input').attr('disabled', customUrlDisabled);
    })

  var $apiPaymentMethodSelect = $('select[name^="MOLLIE_METHOD_API"]');

  $apiPaymentMethodSelect.each(function () {
    togglePaymentMethodDescriptions($(this));
  });

  $apiPaymentMethodSelect.on('change', function () {
    togglePaymentMethodDescriptions($(this));
  });
});

function togglePaymentMethod($button, paymentId) {
    var $clickedButton = $($button);
    $.ajax(ajaxUrl, {
        method: 'POST',
        data: {
            'paymentMethod': paymentId,
            'status' : $clickedButton.data('action'),
            'action': 'togglePaymentMethod',
            'ajax' : 1
        },
        success: function (response) {
            response = JSON.parse(response);
            var checkInputClass = 'icon-check text-success';
            var clearInputClass = 'icon-remove text-danger';
            if (response.success) {
                if (response.paymentStatus) {
                    $clickedButton.data('action', 'deactivate');
                    $clickedButton.find('i').removeClass(clearInputClass).addClass(checkInputClass);
                } else {
                  $clickedButton.data('action', 'activate');
                  $clickedButton.find('i').removeClass(checkInputClass).addClass(clearInputClass);
                }

                if(response.paymentStatus !== undefined) {
                  $clickedButton.closest('.payment-method').find('select[name^="MOLLIE_METHOD_ENABLED"] option[value="' + response.paymentStatus +'"]').prop('selected', true);

                }
            }
        }
    })
}

function togglePaymentMethodDescriptions(apiPaymentMethodSelect){
  if (apiPaymentMethodSelect.val() === 'payments') {
    apiPaymentMethodSelect.closest('.payment-method').find('.payment-api-description').slideDown();
  } else {
    apiPaymentMethodSelect.closest('.payment-method').find('.payment-api-description').slideUp();
  }
}
