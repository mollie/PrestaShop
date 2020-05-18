<?php

namespace _PhpScoper5ea00cc67502b\Container14;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use function class_exists;

/*
 * This file is included in Tests\Dumper\GraphvizDumperTest::testDumpWithFrozenCustomClassContainer
 * and Tests\Dumper\XmlDumperTest::testCompiledContainerCanBeDumped.
 */
if (!class_exists('_PhpScoper5ea00cc67502b\\Container14\\ProjectServiceContainer')) {
    class ProjectServiceContainer extends ContainerBuilder
    {
    }
}
return new ProjectServiceContainer();
