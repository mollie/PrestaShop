<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolvePrivatesPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
class ResolvePrivatesPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testPrivateHasHigherPrecedenceThanPublic()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass')->setPublic(\true)->setPrivate(\true);
        $container->setAlias('bar', 'foo')->setPublic(\false)->setPrivate(\false);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolvePrivatesPass())->process($container);
        $this->assertFalse($container->getDefinition('foo')->isPublic());
        $this->assertFalse($container->getAlias('bar')->isPublic());
    }
}
