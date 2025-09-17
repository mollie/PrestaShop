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

namespace Mollie\Loader;

use Media;
use Mollie;
use Mollie\Adapter\Link;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderManagementAssetLoader implements OrderManagementAssetLoaderInterface
{
    const FILE_NAME = 'OrderManagementAssetLoader';

    private $module;
    private $link;

    public function __construct(
        Mollie $module,
        Link $link
    ) {
        $this->module = $module;
        $this->link = $link;
    }

    public function register($controller, $vars = []): void
    {
        Media::addJsDef([
            'mollieOrderInfoConfig' => [
                'ajax_url' => $this->link->getAdminLink('AdminMollieAjax'),
                'transaction_id' => $vars['transaction_id'],
                'resource' => $vars['resource'],
                'order_id' => $vars['order_id'],
            ],
        ]);

        $controller->addJS($this->module->getPathUri() . 'views/js/admin/order_info.js');
    }
}
