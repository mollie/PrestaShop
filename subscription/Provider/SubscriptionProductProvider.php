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

namespace Mollie\Subscription\Provider;

use Mollie\Subscription\Validator\SubscriptionProductValidator;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionProductProvider
{
    /** @var SubscriptionProductValidator */
    private $subscriptionProductValidator;

    public function __construct(
        SubscriptionProductValidator $subscriptionProductValidator
    ) {
        $this->subscriptionProductValidator = $subscriptionProductValidator;
    }

    public function getProduct(array $products): array
    {
        foreach ($products as $product) {
            if (!$this->subscriptionProductValidator->validate((int) $product['id_product_attribute'])) {
                continue;
            }

            return $product;
        }

        return [];
    }
}
