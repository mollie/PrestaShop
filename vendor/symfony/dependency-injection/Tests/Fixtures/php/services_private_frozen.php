<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\LogicException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use stdClass;
use function class_alias;
use function sprintf;
use function trigger_error;
use const E_USER_DEPRECATED;

/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class ProjectServiceContainer extends Container
{
    private $parameters = [];
    private $targetDirs = [];
    public function __construct()
    {
        $this->services = [];
        $this->methodMap = ['bar_service' => 'getBarServiceService', 'baz_service' => 'getBazServiceService', 'foo_service' => 'getFooServiceService'];
        $this->privates = ['baz_service' => true];
        $this->aliases = [];
    }
    public function getRemovedIds()
    {
        return ['_PhpScoper5ea00cc67502b\\Psr\\Container\\ContainerInterface' => true, '_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => true, 'baz_service' => true];
    }
    public function compile()
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }
    public function isCompiled()
    {
        return true;
    }
    public function isFrozen()
    {
        @trigger_error(sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), E_USER_DEPRECATED);
        return true;
    }
    /**
     * Gets the public 'bar_service' shared service.
     *
     * @return stdClass
     */
    protected function getBarServiceService()
    {
        return $this->services['bar_service'] = new stdClass(${($_ = isset($this->services['baz_service']) ? $this->services['baz_service'] : ($this->services['baz_service'] = new stdClass())) && false ?: '_'});
    }
    /**
     * Gets the public 'foo_service' shared service.
     *
     * @return stdClass
     */
    protected function getFooServiceService()
    {
        return $this->services['foo_service'] = new stdClass(${($_ = isset($this->services['baz_service']) ? $this->services['baz_service'] : ($this->services['baz_service'] = new stdClass())) && false ?: '_'});
    }
    /**
     * Gets the private 'baz_service' shared service.
     *
     * @return stdClass
     */
    protected function getBazServiceService()
    {
        return $this->services['baz_service'] = new stdClass();
    }
}
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class_alias('_PhpScoper5ea00cc67502b\\ProjectServiceContainer', 'ProjectServiceContainer', false);
