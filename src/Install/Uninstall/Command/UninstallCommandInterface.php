<?php

namespace Mollie\Install\Uninstall\Command;

interface UninstallCommandInterface
{
    public function getName(): string;

    public function getCommand(): string;
}
