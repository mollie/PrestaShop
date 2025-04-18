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

use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\MolCustomerRepositoryInterface;
use Mollie\Subscription\Presenter\RecurringOrdersPresenter;

/*
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class mollieSubscriptionsModuleFrontController extends ModuleFrontController
{
    private const FILE_NAME = 'subscriptions';

    /**
     * @var Mollie
     */
    public $module;

    /**
     * @var bool
     */
    public $display_column_right;
    /**
     * @var bool
     */
    public $display_column_left;

    public function initContent()
    {
        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $this->display_column_right = false;
        $this->display_column_left = false;

        if (empty($this->context->customer->id)) {
            Tools::redirect('index.php');
        }

        /** @var MolCustomerRepositoryInterface $molCustomerRepository */
        $molCustomerRepository = $this->module->getService(MolCustomerRepositoryInterface::class);

        /** @var RecurringOrdersPresenter $recurringOrdersPresenter */
        $recurringOrdersPresenter = $this->module->getService(RecurringOrdersPresenter::class);

        /** @var ?\MolCustomer $molCustomer */
        $molCustomer = $molCustomerRepository->findOneBy([
            'email' => $this->context->customer->email,
        ]);

        $this->prepareTemplate(
            $molCustomer ? $recurringOrdersPresenter->present($molCustomer->customer_id) : []
        );

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));
    }

    public function setMedia()
    {
        $js_path = $this->module->getPathUri() . '/views/js/';
        $css_path = $this->module->getPathUri() . '/views/css/';

        parent::setMedia();
        $this->context->controller->addJS($js_path . 'front.js');
        $this->context->controller->addCSS($css_path . 'customerPersonalData.css');
    }

    private function prepareTemplate(array $recurringOrdersPresentData = []): void
    {
        parent::initContent();

        $this->context->smarty->assign([
            'recurringOrdersData' => $recurringOrdersPresentData,
        ]);

        $this->context->smarty->tpl_vars['page']->value['body_classes']['page-customer-account'] = true;

        $this->setTemplate('module:mollie/views/templates/front/subscription/customerSubscriptionsData.tpl');
    }
}
