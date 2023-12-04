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
use Mollie\Subscription\Provider\SubscriptionProductProvider;

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
    /** @var SubscriptionProductProvider */
    private $subscriptionProductProvider;

    public function __construct(
        CartAdapter $cart,
        SubscriptionProductValidator $subscriptionProductValidator,
        ToolsAdapter $tools,
        SubscriptionSettingsValidator $subscriptionSettingsValidator,
        SubscriptionProductProvider $subscriptionProductProvider
    ) {
        $this->cart = $cart;
        $this->subscriptionProductValidator = $subscriptionProductValidator;
        $this->tools = $tools;
        $this->subscriptionSettingsValidator = $subscriptionSettingsValidator;
        $this->subscriptionProductProvider = $subscriptionProductProvider;
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
        $subscriptionProduct = $this->subscriptionProductProvider->getProduct($this->cart->getProducts());

        if (empty($subscriptionProduct)) {
            return true;
        }

        if ((int) $subscriptionProduct['id_product_attribute'] === $productAttributeId) {
            return true;
        }

        return false;
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
