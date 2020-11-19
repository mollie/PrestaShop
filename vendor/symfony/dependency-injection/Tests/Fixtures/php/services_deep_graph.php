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
class Symfony_DI_PhpDumper_Test_Deep_Graph extends \MolliePrefix\Symfony\Component\DependencyInjection\Container
{
    private $parameters = [];
    private $targetDirs = [];
    public function __construct()
    {
        $this->services = [];
        $this->methodMap = ['bar' => 'getBarService', 'foo' => 'getFooService'];
        $this->aliases = [];
    }
    public function getRemovedIds()
    {
        return ['MolliePrefix\\Psr\\Container\\ContainerInterface' => \true, 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => \true];
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
     * Gets the public 'bar' shared service.
     *
     * @return \stdClass
     */
    protected function getBarService()
    {
        $this->services['bar'] = $instance = new \stdClass();
        $instance->p5 = new \stdClass(${($_ = isset($this->services['foo']) ? $this->services['foo'] : $this->getFooService()) && \false ?: '_'});
        return $instance;
    }
    /**
     * Gets the public 'foo' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Dumper\FooForDeepGraph
     */
    protected function getFooService()
    {
        $a = ${($_ = isset($this->services['bar']) ? $this->services['bar'] : $this->getBarService()) && \false ?: '_'};
        if (isset($this->services['foo'])) {
            return $this->services['foo'];
        }
        $b = new \stdClass();
        $c = new \stdClass();
        $c->p3 = new \stdClass();
        $b->p2 = $c;
        return $this->services['foo'] = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Dumper\FooForDeepGraph($a, $b);
    }
}
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
\class_alias('MolliePrefix\\Symfony_DI_PhpDumper_Test_Deep_Graph', 'Symfony_DI_PhpDumper_Test_Deep_Graph', \false);
