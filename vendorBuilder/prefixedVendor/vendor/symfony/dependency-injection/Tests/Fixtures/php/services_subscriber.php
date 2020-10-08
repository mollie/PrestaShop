<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface;
use MolliePrefix\Symfony\Component\DependencyInjection\Container;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\LogicException;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class ProjectServiceContainer extends \MolliePrefix\Symfony\Component\DependencyInjection\Container
{
    private $parameters = [];
    private $targetDirs = [];
    public function __construct()
    {
        $this->services = [];
        $this->normalizedIds = ['MolliePrefix\\symfony\\component\\dependencyinjection\\tests\\fixtures\\customdefinition' => 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition', 'MolliePrefix\\symfony\\component\\dependencyinjection\\tests\\fixtures\\testservicesubscriber' => 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'];
        $this->methodMap = ['MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => 'getCustomDefinitionService', 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber' => 'getTestServiceSubscriberService', 'foo_service' => 'getFooServiceService'];
        $this->privates = ['MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => \true];
        $this->aliases = [];
    }
    public function getRemovedIds()
    {
        return ['MolliePrefix\\Psr\\Container\\ContainerInterface' => \true, 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => \true, 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => \true, 'service_locator.jmktfsv' => \true, 'service_locator.jmktfsv.foo_service' => \true];
    }
    public function compile()
    {
        throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\LogicException('You cannot compile a dumped container that was already compiled.');
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
        return $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'] = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber();
    }
    /**
     * Gets the public 'foo_service' shared autowired service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber
     */
    protected function getFooServiceService()
    {
        return $this->services['foo_service'] = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber((new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => function () {
            $f = function (\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition $v = null) {
                return $v;
            };
            return $f(${($_ = isset($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition']) ? $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition'] : ($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition'] = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition())) && \false ?: '_'});
        }, 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber' => function () {
            $f = function (\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber $v) {
                return $v;
            };
            return $f(${($_ = isset($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber']) ? $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'] : ($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'] = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber())) && \false ?: '_'});
        }, 'bar' => function () {
            $f = function (\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition $v) {
                return $v;
            };
            return $f(${($_ = isset($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber']) ? $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'] : ($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber'] = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber())) && \false ?: '_'});
        }, 'baz' => function () {
            $f = function (\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition $v = null) {
                return $v;
            };
            return $f(${($_ = isset($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition']) ? $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition'] : ($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition'] = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition())) && \false ?: '_'});
        }]))->withContext('foo_service', $this));
    }
    /**
     * Gets the private 'Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition
     */
    protected function getCustomDefinitionService()
    {
        return $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition'] = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition();
    }
}
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
\class_alias('MolliePrefix\\ProjectServiceContainer', 'ProjectServiceContainer', \false);
