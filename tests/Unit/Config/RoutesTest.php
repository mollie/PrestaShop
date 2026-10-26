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

namespace Mollie\Tests\Unit\Config;

use Mollie\Config\Config;
use Mollie\Tests\Unit\BaseTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;

class RoutesTest extends BaseTestCase
{
    /**
     * @dataProvider provideTransactionIds
     */
    public function testViewInDashboardRouteAcceptsMollieTransactionIds(string $transactionId)
    {
        $loader = new YamlFileLoader(new FileLocator(__DIR__ . '/../../../config'));
        $generator = new UrlGenerator($loader->load('routes.yml'), new RequestContext());

        $url = $generator->generate(Config::ROUTE_VIEW_IN_MOLLIE_DASHBOARD, ['transactionId' => $transactionId]);

        $this->assertSame('/mollie/transaction/' . $transactionId . '/view-in-dashboard', $url);
    }

    public function provideTransactionIds()
    {
        return [
            'current payment id' => ['tr_9iZ6WeGmPQMRMdCmGEWYJ'],
            'current order id' => ['ord_VnPHde2zkEsCVFhoYkVYJ'],
            'legacy short order id' => ['ord_iltr91'],
            // Mollie issued dotted order ids in 2023, they stay stored on old shops.
            'legacy dotted order id' => ['ord_1.zih1s5'],
        ];
    }
}
