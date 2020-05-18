<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
class ProjectExtension implements \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
{
    public function load(array $configs, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder $configuration)
    {
        $configuration->setParameter('project.configs', $configs);
        $configs = \array_filter($configs);
        if ($configs) {
            $config = \call_user_func_array('array_merge', $configs);
        } else {
            $config = [];
        }
        $configuration->setDefinition('project.service.bar', new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('FooClass'));
        $configuration->setParameter('project.parameter.bar', isset($config['foo']) ? $config['foo'] : 'foobar');
        $configuration->setDefinition('project.service.foo', new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('FooClass'));
        $configuration->setParameter('project.parameter.foo', isset($config['foo']) ? $config['foo'] : 'foobar');
        return $configuration;
    }
    public function getXsdValidationBasePath()
    {
        return \false;
    }
    public function getNamespace()
    {
        return 'http://www.example.com/schema/project';
    }
    public function getAlias()
    {
        return 'project';
    }
    public function getConfiguration(array $config, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
    }
}
\class_alias('_PhpScoper5ea00cc67502b\\ProjectExtension', 'ProjectExtension', \false);
