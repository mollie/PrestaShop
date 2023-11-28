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

declare(strict_types=1);

namespace Mollie\Subscription\Validator;

use Mollie\Adapter\CartAdapter;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Subscription\Exception\CouldNotValidateSubscriptionSettings;
use Mollie\Subscription\Exception\SubscriptionProductValidationException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CanProductBeAddedToCartValidator
{
    /** @var CartAdapter */
    private $cart;

    /** @var SubscriptionProductValidator */
    private $subscriptionProductValidator;

    /** @var ToolsAdapter */
    private $tools;
    /** @var SubscriptionSettingsValidator */
    private $subscriptionSettingsValidator;

    public function __construct(
        CartAdapter $cart,
        SubscriptionProductValidator $subscriptionProductValidator,
        ToolsAdapter $tools,
        SubscriptionSettingsValidator $subscriptionSettingsValidator
    ) {
        $this->cart = $cart;
        $this->subscriptionProductValidator = $subscriptionProductValidator;
        $this->tools = $tools;
        $this->subscriptionSettingsValidator = $subscriptionSettingsValidator;
    }

    /**
     * @throws SubscriptionProductValidationException
     */
    public function validate(int $productAttributeId): bool
    {
        $isSubscriptionDuplicateProduct = $this->tools->getValue('controller');

        if ($isSubscriptionDuplicateProduct === 'subscriptionWebhook') {
            return true;
        }

        if (!$this->subscriptionProductValidator->validate($productAttributeId)) {
            return true;
        }

        if (!$this->validateSubscriptionSettings()) {
            throw SubscriptionProductValidationException::invalidSubscriptionSettings();
        }

        if (!$this->validateIfSubscriptionProductCanBeAdded($productAttributeId)) {
            throw SubscriptionProductValidationException::cartAlreadyHasSubscriptionProduct();
        }

        return true;
    }

    private function validateIfSubscriptionProductCanBeAdded(int $productAttributeId): bool
    {
        $cartProducts = $this->cart->getProducts();

        foreach ($cartProducts as $cartProduct) {
            if (!$this->subscriptionProductValidator->validate((int) $cartProduct['id_product_attribute'])) {
                continue;
            }

            if ((int) $cartProduct['id_product_attribute'] === $productAttributeId) {
                continue;
            }

            return false;
        }

        return true;
    }

    private function validateSubscriptionSettings(): bool
    {
        try {
            $this->subscriptionSettingsValidator->validate();
        } catch (CouldNotValidateSubscriptionSettings $exception) {
            return false;
        }

        return true;
    }
}
