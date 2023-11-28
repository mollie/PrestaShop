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

namespace Mollie\Subscription\Action;

use Mollie\Subscription\DTO\CreateRecurringOrdersProductData;
use Mollie\Subscription\Exception\CouldNotCreateRecurringOrdersProduct;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Subscription\Logger\LoggerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateRecurringOrdersProductAction
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function run(CreateRecurringOrdersProductData $data): \MolRecurringOrdersProduct
    {
        $this->logger->debug(sprintf('%s - Function called', __METHOD__));

        try {
            $recurringOrdersProduct = new \MolRecurringOrdersProduct();

            $recurringOrdersProduct->id_product = $data->getProductId();
            $recurringOrdersProduct->id_product_attribute = $data->getProductAttributeId();
            $recurringOrdersProduct->quantity = $data->getProductQuantity();
            $recurringOrdersProduct->unit_price = $data->getUnitPriceTaxExcl();

            $recurringOrdersProduct->add();
        } catch (\Throwable $exception) {
            throw CouldNotCreaterecurringOrdersProduct::unknownError($exception);
        }

        $this->logger->debug(sprintf('%s - Function ended', __METHOD__));

        return $recurringOrdersProduct;
    }
}
