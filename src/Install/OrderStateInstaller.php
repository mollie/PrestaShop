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

namespace Mollie\Install;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\DTO\OrderStateData;
use Mollie\Enum\EmailTemplate;
use Mollie\Exception\CouldNotInstallModule;
use Mollie\Factory\ModuleFactory;
use Mollie\Service\OrderStateImageService;
use OrderState;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderStateInstaller implements InstallerInterface
{
    /** @var ModuleFactory */
    private $moduleFactory;
    /** @var ConfigurationAdapter */
    private $configurationAdapter;
    /** @var OrderStateImageService */
    private $orderStateImageService;

    public function __construct(
        ModuleFactory $moduleFactory,
        ConfigurationAdapter $configurationAdapter,
        OrderStateImageService $orderStateImageService
    ) {
        $this->moduleFactory = $moduleFactory;
        $this->configurationAdapter = $configurationAdapter;
        $this->orderStateImageService = $orderStateImageService;
    }

    /**
     * @throws CouldNotInstallModule
     */
    public function install(): bool
    {
        $this->installOrderState(
            Config::MOLLIE_STATUS_PARTIAL_REFUND,
            new OrderStateData(
                'Partially refunded by Mollie',
                '#6F8C9F'
            )
        );

        $this->installOrderState(
            Config::MOLLIE_STATUS_AWAITING,
            new OrderStateData(
                'Awaiting Mollie payment',
                '#4169E1'
            )
        );

        $this->installOrderState(
            Config::MOLLIE_STATUS_PARTIALLY_SHIPPED,
            new OrderStateData(
                'Partially shipped',
                '#8A2BE2'
            )
        );

        $this->installOrderState(
            Config::MOLLIE_STATUS_ORDER_COMPLETED,
            new OrderStateData(
                'Completed',
                '#3d7d1c',
                true
            )
        );

        $this->installOrderState(
            Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED,
            new OrderStateData(
                'Order payment authorized',
                '#8A2BE2',
                true,
                true,
                false,
                true,
                false,
                true,
                EmailTemplate::PAYMENT,
                true
            )
        );

        $this->installOrderState(
            Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED,
            new OrderStateData(
                'Order payment shipped',
                '#8A2BE2',
                true,
                true,
                true,
                false,
                true,
                true,
                EmailTemplate::SHIPPED,
                true
            )
        );

        $this->installOrderState(
            Config::MOLLIE_STATUS_CHARGEBACK,
            new OrderStateData(
                'Mollie Chargeback',
                '#E74C3C'
            )
        );

        return true;
    }

    /**
     * @throws CouldNotInstallModule
     */
    private function installOrderState(string $orderStatus, OrderStateData $orderStateInstallerData): void
    {
        if ($this->validateIfStatusExists($orderStatus)) {
            $this->enableState($orderStatus);

            return;
        }

        $orderState = $this->createOrderState($orderStateInstallerData);

        $this->updateStateConfiguration($orderStatus, $orderState);
    }

    private function validateIfStatusExists(string $key): bool
    {
        $existingStateId = (int) $this->configurationAdapter->get($key);
        $orderState = new OrderState($existingStateId);

        // if state already existed we won't install new one.
        return \Validate::isLoadedObject($orderState);
    }

    private function enableState(string $key): void
    {
        $existingStateId = (int) $this->configurationAdapter->get($key);
        $orderState = new OrderState($existingStateId);

        $this->updateStateConfiguration($key, $orderState);

        if ((bool) !$orderState->deleted) {
            return;
        }

        $orderState->deleted = false;
        $orderState->save();
    }

    /**
     * @throws CouldNotInstallModule
     */
    private function createOrderState(OrderStateData $orderStateInstallerData): OrderState
    {
        $orderState = new OrderState();

        $orderState->send_email = $orderStateInstallerData->isSendEmail();
        $orderState->color = $orderStateInstallerData->getColor();
        $orderState->delivery = $orderStateInstallerData->isDelivery();
        $orderState->logable = $orderStateInstallerData->isLogable();
        $orderState->invoice = $orderStateInstallerData->isInvoice();
        $orderState->module_name = $this->moduleFactory->getModuleName();
        $orderState->shipped = $orderStateInstallerData->isShipped();
        $orderState->paid = $orderStateInstallerData->isPaid();
        $orderState->template = $orderStateInstallerData->getTemplate();
        $orderState->pdf_invoice = $orderStateInstallerData->isPdfInvoice();
        $orderState->hidden = false;
        $orderState->unremovable = false;

        $languages = \Language::getLanguages();

        foreach ($languages as $language) {
            $orderState->name[$language['id_lang']] = $orderStateInstallerData->getName();
        }

        try {
            $orderState->add();
        } catch (\Exception $exception) {
            throw CouldNotInstallModule::failedToInstallOrderState($orderStateInstallerData->getName(), $exception);
        }

        $this->orderStateImageService->createOrderStateLogo($orderState->id);

        return $orderState;
    }

    private function updateStateConfiguration(string $key, OrderState $orderState): void
    {
        $shops = \Shop::getShops();
        foreach ($shops as $shop) {
            $this->configurationAdapter->updateValue($key, (int) $orderState->id, false, null, (int) $shop['id_shop']);
        }
    }
}
