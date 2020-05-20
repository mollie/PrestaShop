<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests\Fixtures\Resource;

use function class_exists;

if (!class_exists(MissingClass::class)) {
    class ConditionalClass
    {
    }
}
