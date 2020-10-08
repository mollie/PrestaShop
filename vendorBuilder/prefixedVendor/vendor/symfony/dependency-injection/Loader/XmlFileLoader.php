<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Loader;

use MolliePrefix\Symfony\Component\Config\Util\XmlUtils;
use MolliePrefix\Symfony\Component\DependencyInjection\Alias;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Expression;
/**
 * XmlFileLoader loads XML files service definitions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class XmlFileLoader extends \MolliePrefix\Symfony\Component\DependencyInjection\Loader\FileLoader
{
    const NS = 'http://symfony.com/schema/dic/services';
    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);
        $xml = $this->parseFileToDOM($path);
        $this->container->fileExists($path);
        $defaults = $this->getServiceDefaults($xml, $path);
        // anonymous services
        $this->processAnonymousServices($xml, $path, $defaults);
        // imports
        $this->parseImports($xml, $path);
        // parameters
        $this->parseParameters($xml, $path);
        // extensions
        $this->loadFromExtensions($xml);
        // services
        try {
            $this->parseDefinitions($xml, $path, $defaults);
        } finally {
            $this->instanceof = [];
        }
    }
    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        if (!\is_string($resource)) {
            return \false;
        }
        if (null === $type && 'xml' === \pathinfo($resource, \PATHINFO_EXTENSION)) {
            return \true;
        }
        return 'xml' === $type;
    }
    /**
     * Parses parameters.
     *
     * @param string $file
     */
    private function parseParameters(\DOMDocument $xml, $file)
    {
        if ($parameters = $this->getChildren($xml->documentElement, 'parameters')) {
            $this->container->getParameterBag()->add($this->getArgumentsAsPhp($parameters[0], 'parameter', $file));
        }
    }
    /**
     * Parses imports.
     *
     * @param string $file
     */
    private function parseImports(\DOMDocument $xml, $file)
    {
        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('container', self::NS);
        if (\false === ($imports = $xpath->query('//container:imports/container:import'))) {
            return;
        }
        $defaultDirectory = \dirname($file);
        foreach ($imports as $import) {
            $this->setCurrentDir($defaultDirectory);
            $this->import($import->getAttribute('resource'), \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($import->getAttribute('type')) ?: null, (bool) \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($import->getAttribute('ignore-errors')), $file);
        }
    }
    /**
     * Parses multiple definitions.
     *
     * @param string $file
     */
    private function parseDefinitions(\DOMDocument $xml, $file, $defaults)
    {
        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('container', self::NS);
        if (\false === ($services = $xpath->query('//container:services/container:service|//container:services/container:prototype'))) {
            return;
        }
        $this->setCurrentDir(\dirname($file));
        $this->instanceof = [];
        $this->isLoadingInstanceof = \true;
        $instanceof = $xpath->query('//container:services/container:instanceof');
        foreach ($instanceof as $service) {
            $this->setDefinition((string) $service->getAttribute('id'), $this->parseDefinition($service, $file, []));
        }
        $this->isLoadingInstanceof = \false;
        foreach ($services as $service) {
            if (null !== ($definition = $this->parseDefinition($service, $file, $defaults))) {
                if ('prototype' === $service->tagName) {
                    $this->registerClasses($definition, (string) $service->getAttribute('namespace'), (string) $service->getAttribute('resource'), (string) $service->getAttribute('exclude'));
                } else {
                    $this->setDefinition((string) $service->getAttribute('id'), $definition);
                }
            }
        }
    }
    /**
     * Get service defaults.
     *
     * @return array
     */
    private function getServiceDefaults(\DOMDocument $xml, $file)
    {
        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('container', self::NS);
        if (null === ($defaultsNode = $xpath->query('//container:services/container:defaults')->item(0))) {
            return [];
        }
        $defaults = ['tags' => $this->getChildren($defaultsNode, 'tag'), 'bind' => \array_map(function ($v) {
            return new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument($v);
        }, $this->getArgumentsAsPhp($defaultsNode, 'bind', $file))];
        foreach ($defaults['tags'] as $tag) {
            if ('' === $tag->getAttribute('name')) {
                throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('The tag name for tag "<defaults>" in "%s" must be a non-empty string.', $file));
            }
        }
        if ($defaultsNode->hasAttribute('autowire')) {
            $defaults['autowire'] = \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($defaultsNode->getAttribute('autowire'));
        }
        if ($defaultsNode->hasAttribute('public')) {
            $defaults['public'] = \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($defaultsNode->getAttribute('public'));
        }
        if ($defaultsNode->hasAttribute('autoconfigure')) {
            $defaults['autoconfigure'] = \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($defaultsNode->getAttribute('autoconfigure'));
        }
        return $defaults;
    }
    /**
     * Parses an individual Definition.
     *
     * @param string $file
     *
     * @return Definition|null
     */
    private function parseDefinition(\DOMElement $service, $file, array $defaults)
    {
        if ($alias = $service->getAttribute('alias')) {
            $this->validateAlias($service, $file);
            $this->container->setAlias((string) $service->getAttribute('id'), $alias = new \MolliePrefix\Symfony\Component\DependencyInjection\Alias($alias));
            if ($publicAttr = $service->getAttribute('public')) {
                $alias->setPublic(\MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($publicAttr));
            } elseif (isset($defaults['public'])) {
                $alias->setPublic($defaults['public']);
            }
            return null;
        }
        if ($this->isLoadingInstanceof) {
            $definition = new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition('');
        } elseif ($parent = $service->getAttribute('parent')) {
            if (!empty($this->instanceof)) {
                throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('The service "%s" cannot use the "parent" option in the same file where "instanceof" configuration is defined as using both is not supported. Move your child definitions to a separate file.', $service->getAttribute('id')));
            }
            foreach ($defaults as $k => $v) {
                if ('tags' === $k) {
                    // since tags are never inherited from parents, there is no confusion
                    // thus we can safely add them as defaults to ChildDefinition
                    continue;
                }
                if ('bind' === $k) {
                    if ($defaults['bind']) {
                        throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Bound values on service "%s" cannot be inherited from "defaults" when a "parent" is set. Move your child definitions to a separate file.', $service->getAttribute('id')));
                    }
                    continue;
                }
                if (!$service->hasAttribute($k)) {
                    throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Attribute "%s" on service "%s" cannot be inherited from "defaults" when a "parent" is set. Move your child definitions to a separate file or define this attribute explicitly.', $k, $service->getAttribute('id')));
                }
            }
            $definition = new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition($parent);
        } else {
            $definition = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition();
            if (isset($defaults['public'])) {
                $definition->setPublic($defaults['public']);
            }
            if (isset($defaults['autowire'])) {
                $definition->setAutowired($defaults['autowire']);
            }
            if (isset($defaults['autoconfigure'])) {
                $definition->setAutoconfigured($defaults['autoconfigure']);
            }
            $definition->setChanges([]);
        }
        foreach (['class', 'public', 'shared', 'synthetic', 'lazy', 'abstract'] as $key) {
            if ($value = $service->getAttribute($key)) {
                $method = 'set' . $key;
                $definition->{$method}(\MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($value));
            }
        }
        if ($value = $service->getAttribute('autowire')) {
            $definition->setAutowired(\MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($value));
        }
        if ($value = $service->getAttribute('autoconfigure')) {
            if (!$definition instanceof \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition) {
                $definition->setAutoconfigured(\MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($value));
            } elseif ($value = \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($value)) {
                throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('The service "%s" cannot have a "parent" and also have "autoconfigure". Try setting autoconfigure="false" for the service.', $service->getAttribute('id')));
            }
        }
        if ($files = $this->getChildren($service, 'file')) {
            $definition->setFile($files[0]->nodeValue);
        }
        if ($deprecated = $this->getChildren($service, 'deprecated')) {
            $definition->setDeprecated(\true, $deprecated[0]->nodeValue ?: null);
        }
        $definition->setArguments($this->getArgumentsAsPhp($service, 'argument', $file, $definition instanceof \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition));
        $definition->setProperties($this->getArgumentsAsPhp($service, 'property', $file));
        if ($factories = $this->getChildren($service, 'factory')) {
            $factory = $factories[0];
            if ($function = $factory->getAttribute('function')) {
                $definition->setFactory($function);
            } else {
                if ($childService = $factory->getAttribute('service')) {
                    $class = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference($childService, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
                } else {
                    $class = $factory->hasAttribute('class') ? $factory->getAttribute('class') : null;
                }
                $definition->setFactory([$class, $factory->getAttribute('method')]);
            }
        }
        if ($configurators = $this->getChildren($service, 'configurator')) {
            $configurator = $configurators[0];
            if ($function = $configurator->getAttribute('function')) {
                $definition->setConfigurator($function);
            } else {
                if ($childService = $configurator->getAttribute('service')) {
                    $class = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference($childService, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
                } else {
                    $class = $configurator->getAttribute('class');
                }
                $definition->setConfigurator([$class, $configurator->getAttribute('method')]);
            }
        }
        foreach ($this->getChildren($service, 'call') as $call) {
            $definition->addMethodCall($call->getAttribute('method'), $this->getArgumentsAsPhp($call, 'argument', $file));
        }
        $tags = $this->getChildren($service, 'tag');
        if (!empty($defaults['tags'])) {
            $tags = \array_merge($tags, $defaults['tags']);
        }
        foreach ($tags as $tag) {
            $parameters = [];
            foreach ($tag->attributes as $name => $node) {
                if ('name' === $name) {
                    continue;
                }
                if (\false !== \strpos($name, '-') && \false === \strpos($name, '_') && !\array_key_exists($normalizedName = \str_replace('-', '_', $name), $parameters)) {
                    $parameters[$normalizedName] = \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($node->nodeValue);
                }
                // keep not normalized key
                $parameters[$name] = \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($node->nodeValue);
            }
            if ('' === $tag->getAttribute('name')) {
                throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('The tag name for service "%s" in "%s" must be a non-empty string.', (string) $service->getAttribute('id'), $file));
            }
            $definition->addTag($tag->getAttribute('name'), $parameters);
        }
        foreach ($this->getChildren($service, 'autowiring-type') as $type) {
            $definition->addAutowiringType($type->textContent);
        }
        $bindings = $this->getArgumentsAsPhp($service, 'bind', $file);
        if (isset($defaults['bind'])) {
            // deep clone, to avoid multiple process of the same instance in the passes
            $bindings = \array_merge(\unserialize(\serialize($defaults['bind'])), $bindings);
        }
        if ($bindings) {
            $definition->setBindings($bindings);
        }
        if ($value = $service->getAttribute('decorates')) {
            $renameId = $service->hasAttribute('decoration-inner-name') ? $service->getAttribute('decoration-inner-name') : null;
            $priority = $service->hasAttribute('decoration-priority') ? $service->getAttribute('decoration-priority') : 0;
            $definition->setDecoratedService($value, $renameId, $priority);
        }
        return $definition;
    }
    /**
     * Parses a XML file to a \DOMDocument.
     *
     * @param string $file Path to a file
     *
     * @return \DOMDocument
     *
     * @throws InvalidArgumentException When loading of XML file returns error
     */
    private function parseFileToDOM($file)
    {
        try {
            $dom = \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::loadFile($file, [$this, 'validateSchema']);
        } catch (\InvalidArgumentException $e) {
            throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Unable to parse file "%s": ', $file) . $e->getMessage(), $e->getCode(), $e);
        }
        $this->validateExtensions($dom, $file);
        return $dom;
    }
    /**
     * Processes anonymous services.
     *
     * @param string $file
     * @param array  $defaults
     */
    private function processAnonymousServices(\DOMDocument $xml, $file, $defaults)
    {
        $definitions = [];
        $count = 0;
        $suffix = '~' . \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder::hash($file);
        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('container', self::NS);
        // anonymous services as arguments/properties
        if (\false !== ($nodes = $xpath->query('//container:argument[@type="service"][not(@id)]|//container:property[@type="service"][not(@id)]|//container:bind[not(@id)]|//container:factory[not(@service)]|//container:configurator[not(@service)]'))) {
            foreach ($nodes as $node) {
                if ($services = $this->getChildren($node, 'service')) {
                    // give it a unique name
                    $id = \sprintf('%d_%s', ++$count, \preg_replace('/^.*\\\\/', '', $services[0]->getAttribute('class')) . $suffix);
                    $node->setAttribute('id', $id);
                    $node->setAttribute('service', $id);
                    $definitions[$id] = [$services[0], $file, \false];
                    $services[0]->setAttribute('id', $id);
                    // anonymous services are always private
                    // we could not use the constant false here, because of XML parsing
                    $services[0]->setAttribute('public', 'false');
                }
            }
        }
        // anonymous services "in the wild"
        if (\false !== ($nodes = $xpath->query('//container:services/container:service[not(@id)]'))) {
            foreach ($nodes as $node) {
                @\trigger_error(\sprintf('Top-level anonymous services are deprecated since Symfony 3.4, the "id" attribute will be required in version 4.0 in %s at line %d.', $file, $node->getLineNo()), \E_USER_DEPRECATED);
                // give it a unique name
                $id = \sprintf('%d_%s', ++$count, \preg_replace('/^.*\\\\/', '', $node->getAttribute('class')) . $suffix);
                $node->setAttribute('id', $id);
                $definitions[$id] = [$node, $file, \true];
            }
        }
        // resolve definitions
        \uksort($definitions, 'strnatcmp');
        foreach (\array_reverse($definitions) as $id => list($domElement, $file, $wild)) {
            if (null !== ($definition = $this->parseDefinition($domElement, $file, $wild ? $defaults : []))) {
                $this->setDefinition($id, $definition);
            }
            if (\true === $wild) {
                $tmpDomElement = new \DOMElement('_services', null, self::NS);
                $domElement->parentNode->replaceChild($tmpDomElement, $domElement);
                $tmpDomElement->setAttribute('id', $id);
            }
        }
    }
    /**
     * Returns arguments as valid php types.
     *
     * @param string $name
     * @param string $file
     *
     * @return mixed
     */
    private function getArgumentsAsPhp(\DOMElement $node, $name, $file, $isChildDefinition = \false)
    {
        $arguments = [];
        foreach ($this->getChildren($node, $name) as $arg) {
            if ($arg->hasAttribute('name')) {
                $arg->setAttribute('key', $arg->getAttribute('name'));
            }
            // this is used by ChildDefinition to overwrite a specific
            // argument of the parent definition
            if ($arg->hasAttribute('index')) {
                $key = ($isChildDefinition ? 'index_' : '') . $arg->getAttribute('index');
            } elseif (!$arg->hasAttribute('key')) {
                // Append an empty argument, then fetch its key to overwrite it later
                $arguments[] = null;
                $keys = \array_keys($arguments);
                $key = \array_pop($keys);
            } else {
                $key = $arg->getAttribute('key');
            }
            $onInvalid = $arg->getAttribute('on-invalid');
            $invalidBehavior = \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
            if ('ignore' == $onInvalid) {
                $invalidBehavior = \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            } elseif ('ignore_uninitialized' == $onInvalid) {
                $invalidBehavior = \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE;
            } elseif ('null' == $onInvalid) {
                $invalidBehavior = \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE;
            }
            switch ($arg->getAttribute('type')) {
                case 'service':
                    if ('' === $arg->getAttribute('id')) {
                        throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Tag "<%s>" with type="service" has no or empty "id" attribute in "%s".', $name, $file));
                    }
                    if ($arg->hasAttribute('strict')) {
                        @\trigger_error(\sprintf('The "strict" attribute used when referencing the "%s" service is deprecated since Symfony 3.3 and will be removed in 4.0.', $arg->getAttribute('id')), \E_USER_DEPRECATED);
                    }
                    $arguments[$key] = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference($arg->getAttribute('id'), $invalidBehavior);
                    break;
                case 'expression':
                    if (!\class_exists(\MolliePrefix\Symfony\Component\ExpressionLanguage\Expression::class)) {
                        throw new \LogicException(\sprintf('The type="expression" attribute cannot be used without the ExpressionLanguage component. Try running "composer require symfony/expression-language".'));
                    }
                    $arguments[$key] = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Expression($arg->nodeValue);
                    break;
                case 'collection':
                    $arguments[$key] = $this->getArgumentsAsPhp($arg, $name, $file);
                    break;
                case 'iterator':
                    $arg = $this->getArgumentsAsPhp($arg, $name, $file);
                    try {
                        $arguments[$key] = new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument($arg);
                    } catch (\MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException $e) {
                        throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Tag "<%s>" with type="iterator" only accepts collections of type="service" references in "%s".', $name, $file));
                    }
                    break;
                case 'tagged':
                    if (!$arg->getAttribute('tag')) {
                        throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Tag "<%s>" with type="tagged" has no or empty "tag" attribute in "%s".', $name, $file));
                    }
                    $arguments[$key] = new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument($arg->getAttribute('tag'));
                    break;
                case 'string':
                    $arguments[$key] = $arg->nodeValue;
                    break;
                case 'constant':
                    $arguments[$key] = \constant(\trim($arg->nodeValue));
                    break;
                default:
                    $arguments[$key] = \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::phpize($arg->nodeValue);
            }
        }
        return $arguments;
    }
    /**
     * Get child elements by name.
     *
     * @param mixed $name
     *
     * @return \DOMElement[]
     */
    private function getChildren(\DOMNode $node, $name)
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === $name && self::NS === $child->namespaceURI) {
                $children[] = $child;
            }
        }
        return $children;
    }
    /**
     * Validates a documents XML schema.
     *
     * @return bool
     *
     * @throws RuntimeException When extension references a non-existent XSD file
     */
    public function validateSchema(\DOMDocument $dom)
    {
        $schemaLocations = ['http://symfony.com/schema/dic/services' => \str_replace('\\', '/', __DIR__ . '/schema/dic/services/services-1.0.xsd')];
        if ($element = $dom->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation')) {
            $items = \preg_split('/\\s+/', $element);
            for ($i = 0, $nb = \count($items); $i < $nb; $i += 2) {
                if (!$this->container->hasExtension($items[$i])) {
                    continue;
                }
                if (($extension = $this->container->getExtension($items[$i])) && \false !== $extension->getXsdValidationBasePath()) {
                    $ns = $extension->getNamespace();
                    $path = \str_replace([$ns, \str_replace('http://', 'https://', $ns)], \str_replace('\\', '/', $extension->getXsdValidationBasePath()) . '/', $items[$i + 1]);
                    if (!\is_file($path)) {
                        throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Extension "%s" references a non-existent XSD file "%s".', \get_class($extension), $path));
                    }
                    $schemaLocations[$items[$i]] = $path;
                }
            }
        }
        $tmpfiles = [];
        $imports = '';
        foreach ($schemaLocations as $namespace => $location) {
            $parts = \explode('/', $location);
            $locationstart = 'file:///';
            if (0 === \stripos($location, 'phar://')) {
                $tmpfile = \tempnam(\sys_get_temp_dir(), 'symfony');
                if ($tmpfile) {
                    \copy($location, $tmpfile);
                    $tmpfiles[] = $tmpfile;
                    $parts = \explode('/', \str_replace('\\', '/', $tmpfile));
                } else {
                    \array_shift($parts);
                    $locationstart = 'phar:///';
                }
            }
            $drive = '\\' === \DIRECTORY_SEPARATOR ? \array_shift($parts) . '/' : '';
            $location = $locationstart . $drive . \implode('/', \array_map('rawurlencode', $parts));
            $imports .= \sprintf('  <xsd:import namespace="%s" schemaLocation="%s" />' . "\n", $namespace, $location);
        }
        $source = <<<EOF
<?xml version="1.0" encoding="utf-8" ?>
<xsd:schema xmlns="http://symfony.com/schema"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    targetNamespace="http://symfony.com/schema"
    elementFormDefault="qualified">

    <xsd:import namespace="http://www.w3.org/XML/1998/namespace"/>
{$imports}
</xsd:schema>
EOF;
        if (\LIBXML_VERSION < 20900) {
            $disableEntities = \libxml_disable_entity_loader(\false);
            $valid = @$dom->schemaValidateSource($source);
            \libxml_disable_entity_loader($disableEntities);
        } else {
            $valid = @$dom->schemaValidateSource($source);
        }
        foreach ($tmpfiles as $tmpfile) {
            @\unlink($tmpfile);
        }
        return $valid;
    }
    /**
     * Validates an alias.
     *
     * @param string $file
     */
    private function validateAlias(\DOMElement $alias, $file)
    {
        foreach ($alias->attributes as $name => $node) {
            if (!\in_array($name, ['alias', 'id', 'public'])) {
                @\trigger_error(\sprintf('Using the attribute "%s" is deprecated for the service "%s" which is defined as an alias in "%s". Allowed attributes for service aliases are "alias", "id" and "public". The XmlFileLoader will raise an exception in Symfony 4.0, instead of silently ignoring unsupported attributes.', $name, $alias->getAttribute('id'), $file), \E_USER_DEPRECATED);
            }
        }
        foreach ($alias->childNodes as $child) {
            if ($child instanceof \DOMElement && self::NS === $child->namespaceURI) {
                @\trigger_error(\sprintf('Using the element "%s" is deprecated for the service "%s" which is defined as an alias in "%s". The XmlFileLoader will raise an exception in Symfony 4.0, instead of silently ignoring unsupported elements.', $child->localName, $alias->getAttribute('id'), $file), \E_USER_DEPRECATED);
            }
        }
    }
    /**
     * Validates an extension.
     *
     * @param string $file
     *
     * @throws InvalidArgumentException When no extension is found corresponding to a tag
     */
    private function validateExtensions(\DOMDocument $dom, $file)
    {
        foreach ($dom->documentElement->childNodes as $node) {
            if (!$node instanceof \DOMElement || 'http://symfony.com/schema/dic/services' === $node->namespaceURI) {
                continue;
            }
            // can it be handled by an extension?
            if (!$this->container->hasExtension($node->namespaceURI)) {
                $extensionNamespaces = \array_filter(\array_map(function ($ext) {
                    return $ext->getNamespace();
                }, $this->container->getExtensions()));
                throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('There is no extension able to load the configuration for "%s" (in "%s"). Looked for namespace "%s", found "%s".', $node->tagName, $file, $node->namespaceURI, $extensionNamespaces ? \sprintf('"%s"', \implode('", "', $extensionNamespaces)) : 'none'));
            }
        }
    }
    /**
     * Loads from an extension.
     */
    private function loadFromExtensions(\DOMDocument $xml)
    {
        foreach ($xml->documentElement->childNodes as $node) {
            if (!$node instanceof \DOMElement || self::NS === $node->namespaceURI) {
                continue;
            }
            $values = static::convertDomElementToArray($node);
            if (!\is_array($values)) {
                $values = [];
            }
            $this->container->loadFromExtension($node->namespaceURI, $values);
        }
    }
    /**
     * Converts a \DOMElement object to a PHP array.
     *
     * The following rules applies during the conversion:
     *
     *  * Each tag is converted to a key value or an array
     *    if there is more than one "value"
     *
     *  * The content of a tag is set under a "value" key (<foo>bar</foo>)
     *    if the tag also has some nested tags
     *
     *  * The attributes are converted to keys (<foo foo="bar"/>)
     *
     *  * The nested-tags are converted to keys (<foo><foo>bar</foo></foo>)
     *
     * @param \DOMElement $element A \DOMElement instance
     *
     * @return mixed
     */
    public static function convertDomElementToArray(\DOMElement $element)
    {
        return \MolliePrefix\Symfony\Component\Config\Util\XmlUtils::convertDomElementToArray($element);
    }
}
