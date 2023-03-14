<?php

namespace Mollie\Subscription\Install;

interface InstallerInterface
{
    /**
     * @return bool
     */
    public function install();

    public function getErrors(): array;
}
