<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\LogicException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class Symfony_DI_PhpDumper_Test_Rot13Parameters extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Container
{
    private $parameters = [];
    private $targetDirs = [];
    public function __construct()
    {
        $this->parameters = $this->getDefaultParameters();
        $this->services = [];
        $this->normalizedIds = ['_PhpScoper5ea00cc67502b\\symfony\\component\\dependencyinjection\\tests\\dumper\\rot13envvarprocessor' => '_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Tests\\Dumper\\Rot13EnvVarProcessor'];
        $this->methodMap = ['_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Tests\\Dumper\\Rot13EnvVarProcessor' => 'getRot13EnvVarProcessorService', 'container.env_var_processors_locator' => 'getContainer_EnvVarProcessorsLocatorService'];
        $this->aliases = [];
    }
    public function getRemovedIds()
    {
        return ['_PhpScoper5ea00cc67502b\\Psr\\Container\\ContainerInterface' => \true, '_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => \true];
    }
    public function compile()
    {
        throw new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\LogicException('You cannot compile a dumped container that was already compiled.');
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
     * Gets the public 'Symfony\Component\DependencyInjection\Tests\Dumper\Rot13EnvVarProcessor' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Dumper\Rot13EnvVarProcessor
     */
    protected function getRot13EnvVarProcessorService()
    {
        return $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Dumper\\Rot13EnvVarProcessor'] = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Dumper\Rot13EnvVarProcessor();
    }
    /**
     * Gets the public 'container.env_var_processors_locator' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\ServiceLocator
     */
    protected function getContainer_EnvVarProcessorsLocatorService()
    {
        return $this->services['container.env_var_processors_locator'] = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceLocator(['rot13' => function () {
            return ${($_ = isset($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Dumper\\Rot13EnvVarProcessor']) ? $this->services['Symfony\\Component\\DependencyInjection\\Tests\\Dumper\\Rot13EnvVarProcessor'] : ($this->services['Symfony\\Component\\DependencyInjection\\Tests\\Dumper\\Rot13EnvVarProcessor'] = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Dumper\Rot13EnvVarProcessor())) && \false ?: '_'};
        }]);
    }
    public function getParameter($name)
    {
        $name = (string) $name;
        if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || \array_key_exists($name, $this->parameters))) {
            $name = $this->normalizeParameterName($name);
            if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || \array_key_exists($name, $this->parameters))) {
                throw new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('The parameter "%s" must be defined.', $name));
            }
        }
        if (isset($this->loadedDynamicParameters[$name])) {
            return $this->loadedDynamicParameters[$name] ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
        }
        return $this->parameters[$name];
    }
    public function hasParameter($name)
    {
        $name = (string) $name;
        $name = $this->normalizeParameterName($name);
        return isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || \array_key_exists($name, $this->parameters);
    }
    public function setParameter($name, $value)
    {
        throw new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\LogicException('Impossible to call set() on a frozen ParameterBag.');
    }
    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $parameters = $this->parameters;
            foreach ($this->loadedDynamicParameters as $name => $loaded) {
                $parameters[$name] = $loaded ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
            }
            $this->parameterBag = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag($parameters);
        }
        return $this->parameterBag;
    }
    private $loadedDynamicParameters = ['hello' => \false];
    private $dynamicParameters = [];
    /**
     * Computes a dynamic parameter.
     *
     * @param string $name The name of the dynamic parameter to load
     *
     * @return mixed The value of the dynamic parameter
     *
     * @throws InvalidArgumentException When the dynamic parameter does not exist
     */
    private function getDynamicParameter($name)
    {
        switch ($name) {
            case 'hello':
                $value = $this->getEnv('rot13:foo');
                break;
            default:
                throw new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('The dynamic parameter "%s" must be defined.', $name));
        }
        $this->loadedDynamicParameters[$name] = \true;
        return $this->dynamicParameters[$name] = $value;
    }
    private $normalizedParameterNames = [];
    private function normalizeParameterName($name)
    {
        if (isset($this->normalizedParameterNames[$normalizedName = \strtolower($name)]) || isset($this->parameters[$normalizedName]) || \array_key_exists($normalizedName, $this->parameters)) {
            $normalizedName = isset($this->normalizedParameterNames[$normalizedName]) ? $this->normalizedParameterNames[$normalizedName] : $normalizedName;
            if ((string) $name !== $normalizedName) {
                @\trigger_error(\sprintf('Parameter names will be made case sensitive in Symfony 4.0. Using "%s" instead of "%s" is deprecated since Symfony 3.4.', $name, $normalizedName), \E_USER_DEPRECATED);
            }
        } else {
            $normalizedName = $this->normalizedParameterNames[$normalizedName] = (string) $name;
        }
        return $normalizedName;
    }
    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return ['env(foo)' => 'jbeyq'];
    }
}
/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
\class_alias('_PhpScoper5ea00cc67502b\\Symfony_DI_PhpDumper_Test_Rot13Parameters', 'Symfony_DI_PhpDumper_Test_Rot13Parameters', \false);
