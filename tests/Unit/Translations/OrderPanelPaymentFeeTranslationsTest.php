<?php
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

namespace Translations;

use PHPUnit\Framework\TestCase;

class OrderPanelPaymentFeeTranslationsTest extends TestCase
{
    public function testOrderPanelTemplateShowsThePaymentFeeRow()
    {
        $template = file_get_contents(dirname(__DIR__, 3) . '/views/templates/hook/order_info.tpl');

        $this->assertStringContainsString("{l s='Payment fee' mod='mollie'}", $template);
        $this->assertStringContainsString('$payment_fee', $template);
    }

    public function testShippedCatalogsTranslateTheOrderPanelPaymentFee()
    {
        $hash = md5('Payment fee');
        $panelKey = '<{mollie}prestashop>order_info_' . $hash;
        $invoiceKey = '<{mollie}prestashop>invoice_fee_' . $hash;

        $catalogs = glob(dirname(__DIR__, 3) . '/translations/*.php');
        $this->assertNotEmpty($catalogs, 'No shipped translation catalogs were found.');

        $checked = 0;
        foreach ($catalogs as $catalog) {
            $iso = basename($catalog, '.php');
            if ('index' === $iso) {
                continue;
            }

            ++$checked;
            $_MODULE = [];
            include $catalog;

            $this->assertArrayHasKey($invoiceKey, $_MODULE, sprintf('Missing invoice "Payment fee" in %s.php', $iso));
            $this->assertArrayHasKey($panelKey, $_MODULE, sprintf('Missing order panel "Payment fee" in %s.php', $iso));
            $this->assertSame($_MODULE[$invoiceKey], $_MODULE[$panelKey], sprintf('Order panel "Payment fee" differs from the invoice in %s.php', $iso));
        }

        $this->assertGreaterThan(1, $checked, 'Expected the shipped locale catalogs to be checked.');
    }
}
