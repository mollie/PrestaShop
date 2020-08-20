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
    $(document).on('click', '.js-mollie-upgrade-tip-close', closeUpgradeNotice);

    function closeUpgradeNotice()
    {
        $.ajax(ajaxUrl, {
            data: {
                action: 'closeUpgradeNotice',
                ajax: 1
            }
        });
    }
});
