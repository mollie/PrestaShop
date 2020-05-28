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
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\PassConfig;
/**
 * @author Guilhem N <egetick@gmail.com>
 */
class PassConfigTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testPassOrdering()
    {
        $config = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\PassConfig();
        $config->setBeforeOptimizationPasses([]);
        $pass1 = $this->getMockBuilder(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::class)->getMock();
        $config->addPass($pass1, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
        $pass2 = $this->getMockBuilder(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::class)->getMock();
        $config->addPass($pass2, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 30);
        $passes = $config->getBeforeOptimizationPasses();
        $this->assertSame($pass2, $passes[0]);
        $this->assertSame($pass1, $passes[1]);
    }
}
