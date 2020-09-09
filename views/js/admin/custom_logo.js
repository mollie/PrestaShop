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
    handleLogoImportButton();
    toggleCustomLogo();
    validateLogo();

    function handleLogoImportButton() {
        $('#MOLLIE_CUSTOM_LOGO-name').on('click', function () {
            $('#MOLLIE_CUSTOM_LOGO').trigger('click');
        });

        $('#MOLLIE_CUSTOM_LOGO-selectbutton').on('click', function () {
            $('#MOLLIE_CUSTOM_LOGO').trigger('click');
        });

        $('#MOLLIE_CUSTOM_LOGO').change(function (e) {
            if ($(this)[0].files !== undefined) {
                var files = $(this)[0].files;
                var name = '';

                $.each(files, function (index, value) {
                    name += value.name + ', ';
                });

                $('#MOLLIE_CUSTOM_LOGO-name').val(name.slice(0, -2));
            } else // Internet Explorer 9 Compatibility
            {
                var name = $(this).val().split(/[\\/]/);
                $('#MOLLIE_CUSTOM_LOGO-name').val(name[name.length - 1]);
            }
        });
    }

    function toggleCustomLogo()
    {
        var $customLogoSelector = $('select[name="MOLLIE_SHOW_CUSTOM_LOGO"]');
        toggleCustomLogoVisibility($customLogoSelector.val());
        $customLogoSelector.on('change', function () {
            toggleCustomLogoVisibility($(this).val());
        });
    }

    function toggleCustomLogoVisibility(showCustomLogo)
    {
        var $customLogoFormGroups = $('.js-form-group-custom-logo');
        $customLogoFormGroups.toggleClass('hidden', showCustomLogo === '0')

    }

    function validateLogo() {
        var _URL = window.URL || window.webkitURL;

        $('#MOLLIE_CUSTOM_LOGO').change(function () {
            var file = $(this)[0].files[0];

            img = new Image();
            var imgwidth = 0;
            var imgheight = 0;
            var maxwidth = 256;
            var maxheight = 64;

            img.src = _URL.createObjectURL(file);
            img.onload = function () {
                imgwidth = this.width;
                imgheight = this.height;

                $("#width").text(imgwidth);
                $("#height").text(imgheight);
                if (imgwidth <= maxwidth && imgheight <= maxheight) {
                    var formData = new FormData();
                    formData.append('fileToUpload', $('#MOLLIE_CUSTOM_LOGO')[0].files[0]);
                    formData.append('action', 'validateLogo');
                    formData.append('ajax', 1);

                    $.ajax(ajaxUrl, {
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        dataType: 'json',
                        processData: false,
                        success: function (response) {
                            if (response.status === 1) {
                                showSuccessMessage(response.message);
                                var $logo = $(".js-mollie-credit-card-custom-logo");
                                var logoUrl = $logo.attr('src');
                                $logo.attr('src', logoUrl + '?' + Math.random());
                                $logo.toggleClass('hidden', false);
                            } else {
                                showErrorMessage(response.message);
                            }
                        }
                    });
                } else {
                    showErrorMessage(image_size_message.replace('%s%', maxwidth).replace('%s1%', maxheight));
                }
            };
            img.onerror = function () {
                showErrorMessage(not_valid_file_message.replace('%s%', file.type));
            }
        });
    }
});
