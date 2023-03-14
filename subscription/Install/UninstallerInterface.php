<?php

namespace Mollie\Subscription\Install;

interface UninstallerInterface
{
    public function uninstall(): bool;

    public function getErrors(): array;
}
