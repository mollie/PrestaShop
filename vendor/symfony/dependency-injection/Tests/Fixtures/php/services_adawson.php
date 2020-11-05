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
        $this->normalizedIds = ['MolliePrefix\\app\\bus' => 'MolliePrefix\\App\\Bus', 'MolliePrefix\\app\\db' => 'MolliePrefix\\App\\Db', 'MolliePrefix\\app\\handler1' => 'MolliePrefix\\App\\Handler1', 'MolliePrefix\\app\\handler2' => 'MolliePrefix\\App\\Handler2', 'MolliePrefix\\app\\processor' => 'MolliePrefix\\App\\Processor', 'MolliePrefix\\app\\registry' => 'MolliePrefix\\App\\Registry', 'MolliePrefix\\app\\schema' => 'MolliePrefix\\App\\Schema'];
        $this->methodMap = ['MolliePrefix\\App\\Bus' => 'getBusService', 'MolliePrefix\\App\\Db' => 'getDbService', 'MolliePrefix\\App\\Handler1' => 'getHandler1Service', 'MolliePrefix\\App\\Handler2' => 'getHandler2Service', 'MolliePrefix\\App\\Processor' => 'getProcessorService', 'MolliePrefix\\App\\Registry' => 'getRegistryService', 'MolliePrefix\\App\\Schema' => 'getSchemaService'];
        $this->privates = ['MolliePrefix\\App\\Handler1' => \true, 'MolliePrefix\\App\\Handler2' => \true, 'MolliePrefix\\App\\Processor' => \true, 'MolliePrefix\\App\\Registry' => \true, 'MolliePrefix\\App\\Schema' => \true];
        $this->aliases = [];
    }
    public function getRemovedIds()
    {
        return ['MolliePrefix\\App\\Handler1' => \true, 'MolliePrefix\\App\\Handler2' => \true, 'MolliePrefix\\App\\Processor' => \true, 'MolliePrefix\\App\\Registry' => \true, 'MolliePrefix\\App\\Schema' => \true, 'MolliePrefix\\Psr\\Container\\ContainerInterface' => \true, 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => \true];
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
     * Gets the public 'App\Bus' shared service.
     *
     * @return \App\Bus
     */
    protected function getBusService()
    {
        $this->services['App\\Bus'] = $instance = new \MolliePrefix\App\Bus(${($_ = isset($this->services['App\\Db']) ? $this->services['App\\Db'] : $this->getDbService()) && \false ?: '_'});
        $instance->handler1 = ${($_ = isset($this->services['App\\Handler1']) ? $this->services['App\\Handler1'] : $this->getHandler1Service()) && \false ?: '_'};
        $instance->handler2 = ${($_ = isset($this->services['App\\Handler2']) ? $this->services['App\\Handler2'] : $this->getHandler2Service()) && \false ?: '_'};
        return $instance;
    }
    /**
     * Gets the public 'App\Db' shared service.
     *
     * @return \App\Db
     */
    protected function getDbService()
    {
        $this->services['App\\Db'] = $instance = new \MolliePrefix\App\Db();
        $instance->schema = ${($_ = isset($this->services['App\\Schema']) ? $this->services['App\\Schema'] : $this->getSchemaService()) && \false ?: '_'};
        return $instance;
    }
    /**
     * Gets the private 'App\Handler1' shared service.
     *
     * @return \App\Handler1
     */
    protected function getHandler1Service()
    {
        $a = ${($_ = isset($this->services['App\\Processor']) ? $this->services['App\\Processor'] : $this->getProcessorService()) && \false ?: '_'};
        if (isset($this->services['App\\Handler1'])) {
            return $this->services['App\\Handler1'];
        }
        return $this->services['App\\Handler1'] = new \MolliePrefix\App\Handler1(${($_ = isset($this->services['App\\Db']) ? $this->services['App\\Db'] : $this->getDbService()) && \false ?: '_'}, ${($_ = isset($this->services['App\\Schema']) ? $this->services['App\\Schema'] : $this->getSchemaService()) && \false ?: '_'}, $a);
    }
    /**
     * Gets the private 'App\Handler2' shared service.
     *
     * @return \App\Handler2
     */
    protected function getHandler2Service()
    {
        $a = ${($_ = isset($this->services['App\\Processor']) ? $this->services['App\\Processor'] : $this->getProcessorService()) && \false ?: '_'};
        if (isset($this->services['App\\Handler2'])) {
            return $this->services['App\\Handler2'];
        }
        return $this->services['App\\Handler2'] = new \MolliePrefix\App\Handler2(${($_ = isset($this->services['App\\Db']) ? $this->services['App\\Db'] : $this->getDbService()) && \false ?: '_'}, ${($_ = isset($this->services['App\\Schema']) ? $this->services['App\\Schema'] : $this->getSchemaService()) && \false ?: '_'}, $a);
    }
    /**
     * Gets the private 'App\Processor' shared service.
     *
     * @return \App\Processor
     */
    protected function getProcessorService()
    {
        $a = ${($_ = isset($this->services['App\\Registry']) ? $this->services['App\\Registry'] : $this->getRegistryService()) && \false ?: '_'};
        if (isset($this->services['App\\Processor'])) {
            return $this->services['App\\Processor'];
        }
        return $this->services['App\\Processor'] = new \MolliePrefix\App\Processor($a, ${($_ = isset($this->services['App\\Db']) ? $this->services['App\\Db'] : $this->getDbService()) && \false ?: '_'});
    }
    /**
     * Gets the private 'App\Registry' shared service.
     *
     * @return \App\Registry
     */
    protected function getRegistryService()
    {
        $this->services['App\\Registry'] = $instance = new \MolliePrefix\App\Registry();
        $instance->processor = [0 => ${($_ = isset($this->services['App\\Db']) ? $this->services['App\\Db'] : $this->getDbService()) && \false ?: '_'}, 1 => ${($_ = isset($this->services['App\\Bus']) ? $this->services['App\\Bus'] : $this->getBusService()) && \false ?: '_'}];
        return $instance;
    }
    /**
     * Gets the private 'App\Schema' shared service.
     *
     * @return \App\Schema
     */
    protected function getSchemaService()
    {
        $a = ${($_ = isset($this->services['App\\Db']) ? $this->services['App\\Db'] : $this->getDbService()) && \false ?: '_'};
        if (isset($this->services['App\\Schema'])) {
            return $this->services['App\\Schema'];
        }
        return $this->services['App\\Schema'] = new \MolliePrefix\App\Schema($a);
    }
}
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
\class_alias('MolliePrefix\\ProjectServiceContainer', 'ProjectServiceContainer', \false);
