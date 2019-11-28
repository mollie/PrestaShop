/**
 * Copyright (c) 2012-2019, Mollie B.V.
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
    var $cardHolder = $('#card-holder');
    if (!$cardHolder.length) {
        return;
    }
    var mollie = Mollie(profileId, {locale: 'nl_NL', testMode: true});

    // var options = {
    //     styles: {
    //         base: {
    //             color: '#000',
    //             fontSize: '10px;',
    //             padding: '10px 15px',
    //             '::placeholder': {
    //                 color: 'rgba(68, 68, 68, 0.2)'
    //             }
    //         }
    //     }
    // };

    var options = {
        styles: {
            base: {
                '::placeholder': {
                    color: 'rgba(68, 68, 68, 0.2)'
                }
            }
        }
    };
    var cardHolder = mollie.createComponent('cardHolder', options);
    cardHolder.mount('#card-holder');

    var cardNumber = mollie.createComponent('cardNumber', options);
    cardNumber.mount('#card-number');

    var expiryDate = mollie.createComponent('expiryDate', options);
    expiryDate.mount('#expiry-date');

    var verificationCode = mollie.createComponent('verificationCode', options);
    verificationCode.mount('#verification-code');

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
            $('input[name="mollieCardToken"]').val(token.token);

            isResubmit = true;
            $form.submit();
        });
    });
});