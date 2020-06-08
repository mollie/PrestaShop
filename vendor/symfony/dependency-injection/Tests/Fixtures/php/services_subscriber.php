<?php

namespace _PhpScoper5eddef0da618a;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerInterface;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Container;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Exception\LogicException;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class ProjectServiceContainer extends \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Container
{
    private $parameters = [];
    private $targetDirs = [];
    public function __construct()
    {
        $this->services = [];
        $this->normalizedIds = ['_PhpScoper5eddef0da618a\\symfony\\component\\dependencyinjection\\tests\\fixtures\\customdefinition' => '_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition', '_PhpScoper5eddef0da618a\\symfony\\component\\dependencyinjection\\tests\\fixtures\\testservicesubscriber' => '_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'];
        $this->methodMap = ['_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => 'getCustomDefinitionService', '_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber' => 'getTestServiceSubscriberService', 'foo_service' => 'getFooServiceService'];
        $this->privates = ['_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => \true];
        $this->aliases = [];
    }
    public function getRemovedIds()
    {
        return ['_PhpScoper5eddef0da618a\\Psr\\Container\\ContainerInterface' => \true, '_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => \true, '_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => \true, 'service_locator.jmktfsv' => \true, 'service_locator.jmktfsv.foo_service' => \true];
    }
    public function compile()
    {
        throw new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Exception\LogicException('You cannot compile a dumped container that was already compiled.');
    }
    public function isCompiled()
    {
        return \true;
    }
    public function isFrozen()
    {
        @\trigger_error(\sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), \E_USER_DEPRECATED);
        return \true;
    }
    /**
     * Gets the public 'Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber
     */
    protected function getTestServiceSubscriberService()
    {
        return $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'] = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber();
    }
    /**
     * Gets the public 'foo_service' shared autowired service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber
     */
    protected function getFooServiceService()
    {
        return $this->services['foo_service'] = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber((new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ServiceLocator(['_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => function () {
            $f = function (\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition $v = null) {
                return $v;
            };
            return $f(${($_ = isset($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition']) ? $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition'] : ($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition'] = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition())) && \false ?: '_'});
        }, '_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber' => function () {
            $f = function (\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber $v) {
                return $v;
            };
            return $f(${($_ = isset($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber']) ? $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'] : ($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'] = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber())) && \false ?: '_'});
        }, 'bar' => function () {
            $f = function (\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition $v) {
                return $v;
            };
            return $f(${($_ = isset($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber']) ? $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'] : ($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'] = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber())) && \false ?: '_'});
        }, 'baz' => function () {
            $f = function (\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition $v = null) {
                return $v;
            };
            return $f(${($_ = isset($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition']) ? $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition'] : ($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition'] = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition())) && \false ?: '_'});
        }]))->withContext('foo_service', $this));
    }
    /**
     * Gets the private 'Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition
     */
    protected function getCustomDefinitionService()
    {
        return $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition'] = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition();
    }
}
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
\class_alias('_PhpScoper5eddef0da618a\\ProjectServiceContainer', 'ProjectServiceContainer', \false);
