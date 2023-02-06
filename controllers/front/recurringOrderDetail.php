<?php
/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

use Mollie\Controller\AbstractMollieController;
use Mollie\Subscription\Handler\FreeOrderCreationHandler;
use Mollie\Subscription\Logger\RecurringOrderPresenter;

class MollieRecurringOrderDetailModuleFrontController extends AbstractMollieController
{
    private const FILE_NAME = 'recurringOrderDetail';

    /**
     * @var Mollie
     */
    public $module;

    /**
     * Start forms process.
     *
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitUpdatePaymentMethod')) {
            $newMethod = Tools::getValue('payment_method');
            $recurringOrderId = Tools::getValue('recurring_order_id');

            if (!$newMethod) {
                $this->errors[] = $this->module->l('Failed to get new payment method.');

                return;
            }

            if (!$recurringOrderId) {
                $this->errors[] = $this->module->l('Failed to get recurring order.');

                return;
            }

            /** @var FreeOrderCreationHandler $freeOrderCreationHandler */
            $freeOrderCreationHandler = $this->module->getService(FreeOrderCreationHandler::class);
            $checkoutUrl = $freeOrderCreationHandler->handle($recurringOrderId, $newMethod);
            Tools::redirect($checkoutUrl);
        }
    }

    /**
     * Assign template vars related to page content.
     *
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        if (Configuration::isCatalogMode()) {
            Tools::redirect('index.php');
        }

        $recurringOrderId = (int) Tools::getValue('id_mol_recurring_order');
        $recurringOrderId = Validate::isUnsignedId($recurringOrderId) ? $recurringOrderId : false;

        if (!$recurringOrderId) {
            Tools::redirect(Context::getContext()->link->getModuleLink($this->module->name, 'subscriptions', [], true));
        }

        /** @var RecurringOrderPresenter $recurringOrderPresenter */
        $recurringOrderPresenter = $this->module->getService(RecurringOrderPresenter::class);

        $this->context->smarty->assign([
            'recurringOrderData' => $recurringOrderPresenter->present($recurringOrderId),
        ]);

        parent::initContent();
        $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/front/subscription/customer_order_detail.css');
        $this->setTemplate('module:mollie/views/templates/front/subscription/customerRecurringOrderDetail.tpl');
    }
}
