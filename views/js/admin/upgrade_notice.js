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
