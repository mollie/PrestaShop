<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
class ProjectExtension implements \MolliePrefix\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
{
    public function load(array $configs, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $configuration)
    {
        $configuration->setParameter('project.configs', $configs);
        $configs = \array_filter($configs);
        if ($configs) {
            $config = \call_user_func_array('array_merge', $configs);
        } else {
            $config = [];
        }
        $configuration->setDefinition('project.service.bar', new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('FooClass'));
        $configuration->setParameter('project.parameter.bar', isset($config['foo']) ? $config['foo'] : 'foobar');
        $configuration->setDefinition('project.service.foo', new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('FooClass'));
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
    public function getConfiguration(array $config, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
    }
}
\class_alias('MolliePrefix\\ProjectExtension', 'ProjectExtension', \false);
