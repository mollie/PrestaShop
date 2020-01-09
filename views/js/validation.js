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

$(document).ready(function() {
    $('#module_form').on('submit', function () {
        var description = $('#MOLLIE_DESCRIPTION');
        var isProfileChecked = $('input[name="MOLLIE_IFRAME"]').prop('checked');
        var profile = $('#MOLLIE_PROFILE_ID');
        var selectedAPI = $('select[name="MOLLIE_API"]').val();
        if (description.val() === '' && selectedAPI === payment_api) {
            event.preventDefault();
            description.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(description_message);
        }
        if (isProfileChecked && profile.val() === '') {
            event.preventDefault();
            profile.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(profile_id_message_empty);
            return;
        }

        if (isProfileChecked && profile.val().substring(0, 4) !== 'pfl_') {
            event.preventDefault();
            profile.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(profile_id_message);
        }
    });

    var $profileSwitch = $('input[name="MOLLIE_IFRAME"]');
    var $profileId = $('#MOLLIE_PROFILE_ID');
    $profileSwitch.on('change', function () {
        if ($profileSwitch.prop('checked')) {
            $profileId.closest('.form-group').show();
        } else {
            $profileId.closest('.form-group').hide();
        }
    })
});
