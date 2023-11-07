<?php

namespace Mollie\Install\Install\Command;

interface InstallCommandInterface
{
    public function getName(): string;

    public function getCommand(): string;
}
