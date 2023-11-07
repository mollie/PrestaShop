<?php

namespace Mollie\Infrastructure\Context;

interface GlobalShopContextInterface
{
    public function getShopId(): int;

    public function getLanguageId(): int;

    public function getLanguageIso(): string;

    public function getCurrencyIso(): string;

    public function getShopDomain(): string;

    public function getShopName(): string;

    public function isShopSingleShopContext(): bool;
}
