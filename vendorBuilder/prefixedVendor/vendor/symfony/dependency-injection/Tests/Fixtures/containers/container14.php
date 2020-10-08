<?php

namespace MolliePrefix\Container14;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
/*
 * This file is included in Tests\Dumper\GraphvizDumperTest::testDumpWithFrozenCustomClassContainer
 * and Tests\Dumper\XmlDumperTest::testCompiledContainerCanBeDumped.
 */
if (!\class_exists('MolliePrefix\\Container14\\ProjectServiceContainer')) {
    class ProjectServiceContainer extends \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder
    {
    }
}
return new \MolliePrefix\Container14\ProjectServiceContainer();
