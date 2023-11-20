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

use Mollie\Controller\AbstractMollieController;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Subscription\Handler\FreeOrderCreationHandler;
use Mollie\Subscription\Handler\SubscriptionCancellationHandler;
use Mollie\Subscription\Presenter\RecurringOrderPresenter;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
            $this->updatePaymentMethod();
        }

        if (Tools::isSubmit('submitCancelSubscriptionMethod')) {
            $this->cancelSubscription();
        }
    }

    /**
     * Assign template vars related to page content.
     *
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $recurringOrderId = (int) Tools::getValue('id_mol_recurring_order');
        $recurringOrderId = Validate::isUnsignedId($recurringOrderId) ? $recurringOrderId : false;

        /** @var RecurringOrderRepositoryInterface $recurringOrderRepository */
        $recurringOrderRepository = $this->module->getService(RecurringOrderRepositoryInterface::class);

        $recurringOrder = $recurringOrderRepository->findOneBy(['id_mol_recurring_order' => $recurringOrderId]);

        if (!Validate::isLoadedObject($recurringOrder) || (int) $recurringOrder->id_customer !== (int) $this->context->customer->id) {
            Tools::redirect(Context::getContext()->link->getModuleLink($this->module->name, 'subscriptions', [], true));
        }

        /** @var PrestaLoggerInterface $logger */
        $logger = $this->module->getService(PrestaLoggerInterface::class);

        /** @var RecurringOrderPresenter $recurringOrderPresenter */
        $recurringOrderPresenter = $this->module->getService(RecurringOrderPresenter::class);

        try {
            $this->context->smarty->assign([
                'recurringOrderData' => $recurringOrderPresenter->present($recurringOrderId),
                'token' => Tools::getToken(),
            ]);
        } catch (Throwable $exception) {
            $logger->error('Failed to present subscription order', [
                'Exception message' => $exception->getMessage(),
                'Exception code' => $exception->getCode(),
            ]);

            Tools::redirect(Context::getContext()->link->getModuleLink($this->module->name, 'subscriptions', [], true));
        }

        parent::initContent();
        $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/front/subscription/customer_order_detail.css');
        $this->setTemplate('module:mollie/views/templates/front/subscription/customerRecurringOrderDetail.tpl');
    }

    private function updatePaymentMethod(): void
    {
        $newMethod = Tools::getValue('payment_method');
        $recurringOrderId = Tools::getValue('recurring_order_id');

        if (!$this->validateToken()) {
            $this->errors[] = $this->module->l('Error: token invalid.', self::FILE_NAME);

            return;
        }

        if (!$newMethod) {
            $this->errors[] = $this->module->l('Failed to get new payment method.', self::FILE_NAME);

            return;
        }

        if (!$recurringOrderId) {
            $this->errors[] = $this->module->l('Failed to get recurring order.', self::FILE_NAME);

            return;
        }

        /** @var FreeOrderCreationHandler $freeOrderCreationHandler */
        $freeOrderCreationHandler = $this->module->getService(FreeOrderCreationHandler::class);
        $checkoutUrl = $freeOrderCreationHandler->handle($recurringOrderId, $newMethod);

        Tools::redirect($checkoutUrl);
    }

    private function cancelSubscription(): void
    {
        $recurringOrderId = Tools::getValue('recurring_order_id');

        if (!$this->validateToken()) {
            $this->errors[] = $this->module->l('Error: token invalid.', self::FILE_NAME);

            return;
        }

        if (!$recurringOrderId) {
            $this->errors[] = $this->module->l('Failed to get recurring order.', self::FILE_NAME);

            return;
        }

        /** @var SubscriptionCancellationHandler $subscriptionCancellationHandler */
        $subscriptionCancellationHandler = $this->module->getService(SubscriptionCancellationHandler::class);

        $subscriptionCancellationHandler->handle($recurringOrderId);
        $this->success[] = $this->module->l('Successfully canceled subscription.', self::FILE_NAME);
    }

    private function validateToken(): bool
    {
        if (!Configuration::get('PS_TOKEN_ENABLE')) {
            return true;
        }

        return strcasecmp(Tools::getToken(), Tools::getValue('token')) == 0;
    }
}
