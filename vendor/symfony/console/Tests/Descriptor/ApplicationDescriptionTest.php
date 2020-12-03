<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Descriptor;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Application;
use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Descriptor\ApplicationDescription;
final class ApplicationDescriptionTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getNamespacesProvider
     */
    public function testGetNamespaces(array $expected, array $names)
    {
        $application = new \MolliePrefix\Symfony\Component\Console\Tests\Descriptor\TestApplication();
        foreach ($names as $name) {
            $application->add(new \MolliePrefix\Symfony\Component\Console\Command\Command($name));
        }
        $this->assertSame($expected, \array_keys((new \MolliePrefix\Symfony\Component\Console\Descriptor\ApplicationDescription($application))->getNamespaces()));
    }
    public function getNamespacesProvider()
    {
        return [[['_global'], ['foobar']], [['a', 'b'], ['b:foo', 'a:foo', 'b:bar']], [['_global', 'b', 'z', 22, 33], ['z:foo', '1', '33:foo', 'b:foo', '22:foo:bar']]];
    }
}
final class TestApplication extends \MolliePrefix\Symfony\Component\Console\Application
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return [];
    }
}
