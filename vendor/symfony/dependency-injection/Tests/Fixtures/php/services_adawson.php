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
        $this->normalizedIds = ['_PhpScoper5eddef0da618a\\app\\bus' => '_PhpScoper5eddef0da618a\\App\\Bus', '_PhpScoper5eddef0da618a\\app\\db' => '_PhpScoper5eddef0da618a\\App\\Db', '_PhpScoper5eddef0da618a\\app\\handler1' => '_PhpScoper5eddef0da618a\\App\\Handler1', '_PhpScoper5eddef0da618a\\app\\handler2' => '_PhpScoper5eddef0da618a\\App\\Handler2', '_PhpScoper5eddef0da618a\\app\\processor' => '_PhpScoper5eddef0da618a\\App\\Processor', '_PhpScoper5eddef0da618a\\app\\registry' => '_PhpScoper5eddef0da618a\\App\\Registry', '_PhpScoper5eddef0da618a\\app\\schema' => '_PhpScoper5eddef0da618a\\App\\Schema'];
        $this->methodMap = ['_PhpScoper5eddef0da618a\\App\\Bus' => 'getBusService', '_PhpScoper5eddef0da618a\\App\\Db' => 'getDbService', '_PhpScoper5eddef0da618a\\App\\Handler1' => 'getHandler1Service', '_PhpScoper5eddef0da618a\\App\\Handler2' => 'getHandler2Service', '_PhpScoper5eddef0da618a\\App\\Processor' => 'getProcessorService', '_PhpScoper5eddef0da618a\\App\\Registry' => 'getRegistryService', '_PhpScoper5eddef0da618a\\App\\Schema' => 'getSchemaService'];
        $this->privates = ['_PhpScoper5eddef0da618a\\App\\Handler1' => \true, '_PhpScoper5eddef0da618a\\App\\Handler2' => \true, '_PhpScoper5eddef0da618a\\App\\Processor' => \true, '_PhpScoper5eddef0da618a\\App\\Registry' => \true, '_PhpScoper5eddef0da618a\\App\\Schema' => \true];
        $this->aliases = [];
    }
    public function getRemovedIds()
    {
        return ['_PhpScoper5eddef0da618a\\App\\Handler1' => \true, '_PhpScoper5eddef0da618a\\App\\Handler2' => \true, '_PhpScoper5eddef0da618a\\App\\Processor' => \true, '_PhpScoper5eddef0da618a\\App\\Registry' => \true, '_PhpScoper5eddef0da618a\\App\\Schema' => \true, '_PhpScoper5eddef0da618a\\Psr\\Container\\ContainerInterface' => \true, '_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => \true];
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
     * Gets the public 'App\Bus' shared service.
     *
     * @return \App\Bus
     */
    protected function getBusService()
    {
        $this->services['App\\Bus'] = $instance = new \_PhpScoper5eddef0da618a\App\Bus(${($_ = isset($this->services['App\\Db']) ? $this->services['App\\Db'] : $this->getDbService()) && \false ?: '_'});
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
        $this->services['App\\Db'] = $instance = new \_PhpScoper5eddef0da618a\App\Db();
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
        return $this->services['App\\Handler1'] = new \_PhpScoper5eddef0da618a\App\Handler1(${($_ = isset($this->services['App\\Db']) ? $this->services['App\\Db'] : $this->getDbService()) && \false ?: '_'}, ${($_ = isset($this->services['App\\Schema']) ? $this->services['App\\Schema'] : $this->getSchemaService()) && \false ?: '_'}, $a);
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
        return $this->services['App\\Handler2'] = new \_PhpScoper5eddef0da618a\App\Handler2(${($_ = isset($this->services['App\\Db']) ? $this->services['App\\Db'] : $this->getDbService()) && \false ?: '_'}, ${($_ = isset($this->services['App\\Schema']) ? $this->services['App\\Schema'] : $this->getSchemaService()) && \false ?: '_'}, $a);
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
        return $this->services['App\\Processor'] = new \_PhpScoper5eddef0da618a\App\Processor($a, ${($_ = isset($this->services['App\\Db']) ? $this->services['App\\Db'] : $this->getDbService()) && \false ?: '_'});
    }
    /**
     * Gets the private 'App\Registry' shared service.
     *
     * @return \App\Registry
     */
    protected function getRegistryService()
    {
        $this->services['App\\Registry'] = $instance = new \_PhpScoper5eddef0da618a\App\Registry();
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
        return $this->services['App\\Schema'] = new \_PhpScoper5eddef0da618a\App\Schema($a);
    }
}
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
\class_alias('_PhpScoper5eddef0da618a\\ProjectServiceContainer', 'ProjectServiceContainer', \false);
