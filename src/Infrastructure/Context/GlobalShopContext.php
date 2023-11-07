<?php

namespace Mollie\Infrastructure\Context;

use Mollie\Adapter\Context;

final class GlobalShopContext implements GlobalShopContextInterface
{
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function getShopId(): int
    {
        return $this->context->getShopId();
    }

    public function getLanguageId(): int
    {
        return $this->context->getLanguageId();
    }

    public function getLanguageIso(): string
    {
        return $this->context->getLanguageIso();
    }

    public function getCurrencyIso(): string
    {
        return $this->context->getCurrencyIso();
    }

    public function getShopDomain(): string
    {
        return $this->context->getShopDomain();
    }

    public function getShopName(): string
    {
        return $this->context->getShopName();
    }

    public function isShopSingleShopContext(): bool
    {
        return \Shop::getContext() === \Shop::CONTEXT_SHOP;
    }
}
