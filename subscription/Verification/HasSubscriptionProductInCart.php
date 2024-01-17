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

namespace Mollie\Subscription\Verification;

use Mollie\Adapter\Context;
use Mollie\Subscription\Validator\SubscriptionProductValidator;

if (!defined('_PS_VERSION_')) {
    exit;
}

class HasSubscriptionProductInCart
{
    /** @var Context */
    private $context;
    /** @var SubscriptionProductValidator */
    private $subscriptionProductValidator;

    public function __construct(
        Context $context,
        SubscriptionProductValidator $subscriptionProductValidator
    ) {
        $this->context = $context;
        $this->subscriptionProductValidator = $subscriptionProductValidator;
    }

    public function verify(): bool
    {
        $cartProducts = $this->context->getCartProducts();

        foreach ($cartProducts as $cartProduct) {
            if ($this->subscriptionProductValidator->validate((int) $cartProduct['id_product_attribute'])) {
                return true;
            }
        }

        return false;
    }
}
