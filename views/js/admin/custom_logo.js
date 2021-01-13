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
                                $logo.attr('src', logoUrl + '?' + $.now());
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
