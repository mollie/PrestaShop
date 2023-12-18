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

namespace Mollie\Subscription\DTO\Mail;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GeneralSubscriptionMailData
{
    /** @var string */
    private $mollieSubscriptionId;
    /** @var string */
    private $productName;
    /** @var float */
    private $productUnitPriceTaxExcl;
    /** @var int */
    private $productQuantity;
    /** @var float */
    private $totalOrderPriceTaxIncl;
    /** @var string */
    private $firstName;
    /** @var string */
    private $lastName;
    /** @var string */
    private $customerEmail;
    /** @var int */
    private $langId;
    /** @var int */
    private $shopId;

    public function __construct(
        string $mollieSubscriptionId,
        string $productName,
        float $productUnitPriceTaxExcl,
        int $productQuantity,
        float $totalOrderPriceTaxIncl,
        string $firstName,
        string $lastName,
        string $customerEmail,
        int $langId,
        int $shopId
    ) {
        $this->mollieSubscriptionId = $mollieSubscriptionId;
        $this->productName = $productName;
        $this->productUnitPriceTaxExcl = $productUnitPriceTaxExcl;
        $this->productQuantity = $productQuantity;
        $this->totalOrderPriceTaxIncl = $totalOrderPriceTaxIncl;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->customerEmail = $customerEmail;
        $this->langId = $langId;
        $this->shopId = $shopId;
    }

    /**
     * @return string
     */
    public function getMollieSubscriptionId(): string
    {
        return $this->mollieSubscriptionId;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * @return float
     */
    public function getProductUnitPriceTaxExcl(): float
    {
        return $this->productUnitPriceTaxExcl;
    }

    /**
     * @return int
     */
    public function getProductQuantity(): int
    {
        return $this->productQuantity;
    }

    /**
     * @return float
     */
    public function getTotalOrderPriceTaxIncl(): float
    {
        return $this->totalOrderPriceTaxIncl;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    /**
     * @return int
     */
    public function getLangId(): int
    {
        return $this->langId;
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function toArray(): array
    {
        return [
            'subscription_reference' => $this->getMollieSubscriptionId(),
            'product_name' => $this->getProductName(),
            'unit_price' => $this->getProductUnitPriceTaxExcl(),
            'quantity' => $this->getProductQuantity(),
            'total_price' => $this->getTotalOrderPriceTaxIncl(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
        ];
    }

    public static function create(
        string $mollieSubscriptionId,
        string $productName,
        float $productUnitPriceTaxExcl,
        int $productQuantity,
        float $totalOrderPriceTaxIncl,
        string $firstName,
        string $lastName,
        string $customerEmail,
        int $langId,
        int $shopId
    ): self {
        return new self(
            $mollieSubscriptionId,
            $productName,
            $productUnitPriceTaxExcl,
            $productQuantity,
            $totalOrderPriceTaxIncl,
            $firstName,
            $lastName,
            $customerEmail,
            $langId,
            $shopId
        );
    }
}
