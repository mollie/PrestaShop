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
        $this->methodMap = ['bar_service' => 'getBarServiceService', 'baz_service' => 'getBazServiceService', 'foo_service' => 'getFooServiceService'];
        $this->privates = ['baz_service' => \true];
        $this->aliases = [];
    }
    public function getRemovedIds()
    {
        return ['MolliePrefix\\Psr\\Container\\ContainerInterface' => \true, 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => \true, 'baz_service' => \true];
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
     * Gets the public 'bar_service' shared service.
     *
     * @return \stdClass
     */
    protected function getBarServiceService()
    {
        return $this->services['bar_service'] = new \stdClass(${($_ = isset($this->services['baz_service']) ? $this->services['baz_service'] : ($this->services['baz_service'] = new \stdClass())) && \false ?: '_'});
    }
    /**
     * Gets the public 'foo_service' shared service.
     *
     * @return \stdClass
     */
    protected function getFooServiceService()
    {
        return $this->services['foo_service'] = new \stdClass(${($_ = isset($this->services['baz_service']) ? $this->services['baz_service'] : ($this->services['baz_service'] = new \stdClass())) && \false ?: '_'});
    }
    /**
     * Gets the private 'baz_service' shared service.
     *
     * @return \stdClass
     */
    protected function getBazServiceService()
    {
        return $this->services['baz_service'] = new \stdClass();
    }
}
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
\class_alias('MolliePrefix\\ProjectServiceContainer', 'ProjectServiceContainer', \false);
