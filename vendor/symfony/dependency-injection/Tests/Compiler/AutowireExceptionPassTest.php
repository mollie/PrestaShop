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
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowireExceptionPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowirePass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;
/**
 * @group legacy
 */
class AutowireExceptionPassTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testThrowsException()
    {
        $autowirePass = $this->getMockBuilder(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowirePass::class)->getMock();
        $autowireException = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException('foo_service_id', 'An autowiring exception message');
        $autowirePass->expects($this->any())->method('getAutowiringExceptions')->willReturn([$autowireException]);
        $inlinePass = $this->getMockBuilder(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass::class)->getMock();
        $inlinePass->expects($this->any())->method('getInlinedServiceIds')->willReturn([]);
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo_service_id');
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowireExceptionPass($autowirePass, $inlinePass);
        try {
            $pass->process($container);
            $this->fail('->process() should throw the exception if the service id exists');
        } catch (\Exception $e) {
            $this->assertSame($autowireException, $e);
        }
    }
    public function testThrowExceptionIfServiceInlined()
    {
        $autowirePass = $this->getMockBuilder(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowirePass::class)->getMock();
        $autowireException = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException('a_service', 'An autowiring exception message');
        $autowirePass->expects($this->any())->method('getAutowiringExceptions')->willReturn([$autowireException]);
        $inlinePass = $this->getMockBuilder(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass::class)->getMock();
        $inlinePass->expects($this->any())->method('getInlinedServiceIds')->willReturn([
            // a_service inlined into b_service
            'a_service' => ['b_service'],
            // b_service inlined into c_service
            'b_service' => ['c_service'],
        ]);
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        // ONLY register c_service in the final container
        $container->register('c_service', 'stdClass');
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowireExceptionPass($autowirePass, $inlinePass);
        try {
            $pass->process($container);
            $this->fail('->process() should throw the exception if the service id exists');
        } catch (\Exception $e) {
            $this->assertSame($autowireException, $e);
        }
    }
    public function testDoNotThrowExceptionIfServiceInlinedButRemoved()
    {
        $autowirePass = $this->getMockBuilder(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowirePass::class)->getMock();
        $autowireException = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException('a_service', 'An autowiring exception message');
        $autowirePass->expects($this->any())->method('getAutowiringExceptions')->willReturn([$autowireException]);
        $inlinePass = $this->getMockBuilder(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass::class)->getMock();
        $inlinePass->expects($this->any())->method('getInlinedServiceIds')->willReturn([
            // a_service inlined into b_service
            'a_service' => ['b_service'],
            // b_service inlined into c_service
            'b_service' => ['c_service'],
        ]);
        // do NOT register c_service in the container
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowireExceptionPass($autowirePass, $inlinePass);
        $pass->process($container);
        // mark the test as passed
        $this->assertTrue(\true);
    }
    public function testNoExceptionIfServiceRemoved()
    {
        $autowirePass = $this->getMockBuilder(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowirePass::class)->getMock();
        $autowireException = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException('non_existent_service');
        $autowirePass->expects($this->any())->method('getAutowiringExceptions')->willReturn([$autowireException]);
        $inlinePass = $this->getMockBuilder(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass::class)->getMock();
        $inlinePass->expects($this->any())->method('getInlinedServiceIds')->willReturn([]);
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowireExceptionPass($autowirePass, $inlinePass);
        $pass->process($container);
        // mark the test as passed
        $this->assertTrue(\true);
    }
}
