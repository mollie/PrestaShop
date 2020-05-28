<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Container;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerInterface;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Container;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\LogicException;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class ProjectServiceContainer extends \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Container\ConstructorWithOptionalArgumentsContainer
{
    private $parameters = [];
    private $targetDirs = [];
    public function __construct()
    {
        parent::__construct();
        $this->parameterBag = null;
        $this->services = [];
        $this->aliases = [];
    }
    public function getRemovedIds()
    {
        return ['_PhpScoper5ece82d7231e4\\Psr\\Container\\ContainerInterface' => \true, '_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => \true];
    }
    public function compile()
    {
        throw new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Exception\LogicException('You cannot compile a dumped container that was already compiled.');
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
}
