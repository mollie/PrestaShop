<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolvePrivatesPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
class ResolvePrivatesPassTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testPrivateHasHigherPrecedenceThanPublic()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass')->setPublic(\true)->setPrivate(\true);
        $container->setAlias('bar', 'foo')->setPublic(\false)->setPrivate(\false);
        (new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolvePrivatesPass())->process($container);
        $this->assertFalse($container->getDefinition('foo')->isPublic());
        $this->assertFalse($container->getAlias('bar')->isPublic());
    }
}
