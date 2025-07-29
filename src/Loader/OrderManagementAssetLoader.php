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

use Cart;
use Currency;
use Media;
use Mollie;
use Mollie\Adapter\Link;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Utility\TransactionUtility;
use Order;
use Tools;
use Context;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderManagementAssetLoader implements LoaderInterface
{
    const FILE_NAME = 'OrderManagementAssetLoader';

    private $module;
    private $paymentMethodRepository;
    private $link;

    public function __construct(
        Mollie $module,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        Link $link
    ) {
        $this->module = $module;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->link = $link;
    }

    public function register(): void
    {
        Media::addJsDef([
            'mollieOrderInfoConfig' => [
                'ajax_url' => $this->link->getAdminLink('AdminMollieAjax'),
                'transaction_id' => $mollieTransactionId,
                'resource' => $mollieApiType,
                'order_id' => $orderId,
            ],
        ]);

        Context::getContext()->controller->addJS($this->module->getPathUri() . 'views/js/admin/order_info.js');
    }

    public function loadOrderListAssets(): void
    {
        Media::addJsDef([
            'mollieHookAjaxUrl' => $this->link->getAdminLink('AdminMollieAjax'),
        ]);

        Context::getContext()->controller->addCSS($this->module->getPathUri() . 'views/css/admin/order-list.css');
        Context::getContext()->controller->addJS($this->module->getPathUri() . 'views/js/admin/order_list.js');

        if (Tools::isSubmit('addorder') || version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            Media::addJsDef([
                'molliePendingStatus' => \Configuration::get(\Mollie\Config\Config::MOLLIE_STATUS_AWAITING),
                'isPsVersion177' => version_compare(_PS_VERSION_, '1.7.7.0', '>='),
            ]);
            Context::getContext()->controller->addJS($this->module->getPathUri() . 'views/js/admin/order_add.js');
        }
    }

    public function loadOrderGridAssets(): void
    {
        Context::getContext()->smarty->assign([
            'mollieProcessUrl' => $this->link->getAdminLink('AdminModules', true) . '&configure=mollie&ajax=1',
            'mollieCheckMethods' => \Mollie\Utility\TimeUtility::getCurrentTimeStamp() > ((int) \Configuration::get(\Mollie\Config\Config::MOLLIE_METHODS_LAST_CHECK) + \Mollie\Config\Config::MOLLIE_METHODS_CHECK_INTERVAL),
        ]);

        Context::getContext()->controller->addJS($this->module->getPathUri() . 'views/js/admin/order_info.js');
    }
}