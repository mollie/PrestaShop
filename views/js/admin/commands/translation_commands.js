/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
$('table.table tr td:nth-child(1)').each(function () {
        var $textField = $(this);
        var $input = $($textField.next().next()).find(':input');
        $input.val($textField.html())
    }
)
