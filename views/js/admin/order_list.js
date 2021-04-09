/*
 * NOTICE OF LICENSE
 *
 * @author    INVERTUS, UAB www.invertus.eu <support@invertus.eu>
 * @copyright Copyright (c) permanent, INVERTUS, UAB
 * @license   Addons PrestaShop license limitation
 * @see       /LICENSE
 *
 *  International Registered Trademark & Property of INVERTUS, UAB
 */

$(document).ready(function () {
    $(document).on('click', '.resend-payment-mail-mollie', function () {
        // toggleMessageBoxPrinted();
        $.ajax(mollieHookAjaxUrl, {
            data: {
                id_order: $(this).data('id-order'),
                action: 'resendPaymentMail',
                ajax: 1
            },
            success: function (response) {
                response = JSON.parse(response);
                // toggleMessageBoxResult(response.success, response.message);

                // $.each(response.icon_replacers, function (key, value) {
                //     replaceIcon(value.id_order, value.icon_replacer);
                // })

                if (response.success) {
                   showSuccessMessage(response.message);
                    return;
                }

                if (!response.success) {
                    showErrorMessage(response.message);
                    return;
                }


            },
            error: function(xhr, ajaxOptions, thrownError) {
                toggleMessageBoxResult(false, xhr.statusText);
            }
        });
        return false;
    });

    function toggleMessageBoxPrinted()
    {
        messageBox = $('.message-text-box');
        messageBox.html(shipmentIsBeingPrintedMessage);
        messageBox.addClass('alert-warning');
        messageBox.removeClass('alert-danger');
        messageBox.removeClass('alert-success');
        $("html, body").animate({ scrollTop: 0 }, "slow");
        $('.label-printing-message').show();
    }

    function toggleMessageBoxResult(success, message) {
        messageBox.html(message);
        if (success) {
            messageBox.removeClass('alert-warning');
            messageBox.addClass('alert-success');
        }

        if (!success) {
            messageBox.removeClass('alert-warning');
            messageBox.addClass('alert-danger');
        }
    }

    function replaceIcon(idOrder, iconReplacer)
    {
        $('.mollie-icon-container[data-id-order="' + idOrder + '"]').html(iconReplacer);
    }

    $(document).on('click', '#download-selected-labels', function (event) {
        event.preventDefault();
        var selectedOrderIdArray = new Array();

        $(".row-selector input:checked").each(function()
        {
            selectedOrderIdArray.push($(this).val());
        });

        toggleMessageBoxPrinted();
        if (selectedOrderIdArray.length == 0) {
            toggleMessageBoxResult(false, noOrdersSelectedMessage);
            return;
        }
        $.ajax(mollieHookAjaxUrl, {
            data: {
                orders: JSON.stringify(selectedOrderIdArray),
                action: 'saveMultipleLabelsFromOrderList',
                ajax: 1
            },
            success: function (response) {
                response = JSON.parse(response);
                toggleMessageBoxResult(response.success, response.message);

                $.each(response.icon_replacers, function (key, value) {
                    replaceIcon(value.id_order, value.icon_replacer);
                });

                if (response.success) {
                    var labelPrintUrl = mollieHookAjaxUrl +
                        '&action=downloadLabelFromManifests' +
                        '&manifests= ' + JSON.stringify(response.manifests) +
                        '&ajax=1';
                    window.location = labelPrintUrl;
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                toggleMessageBoxResult(false, xhr.statusText)
            }
        });
    });

    $('.bulk-actions .dropdown-menu').append('<li><a href="#" id="download-selected-labels" target="_blank"><i class="icon-download"></i> Print Labels</a></li>');
});
