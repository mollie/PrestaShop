<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace MolliePrefix\PhpCsFixer;

use MolliePrefix\PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use MolliePrefix\PhpCsFixer\ConfigurationException\InvalidForEnvFixerConfigurationException;
use MolliePrefix\PhpCsFixer\ConfigurationException\RequiredFixerConfigurationException;
use MolliePrefix\PhpCsFixer\Console\Application;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurableFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\DefinedFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\FixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\DeprecatedFixerOption;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\InvalidOptionsForEnvException;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use MolliePrefix\Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
abstract class AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\FixerInterface, \MolliePrefix\PhpCsFixer\Fixer\DefinedFixerInterface
{
    /**
     * @var null|array<string, mixed>
     */
    protected $configuration;
    /**
     * @var WhitespacesFixerConfig
     */
    protected $whitespacesConfig;
    /**
     * @var null|FixerConfigurationResolverInterface
     */
    private $configurationDefinition;
    public function __construct()
    {
        if ($this instanceof \MolliePrefix\PhpCsFixer\Fixer\ConfigurableFixerInterface) {
            try {
                $this->configure([]);
            } catch (\MolliePrefix\PhpCsFixer\ConfigurationException\RequiredFixerConfigurationException $e) {
                // ignore
            }
        }
        if ($this instanceof \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface) {
            $this->whitespacesConfig = $this->getDefaultWhitespacesFixerConfig();
        }
    }
    public final function fix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if ($this instanceof \MolliePrefix\PhpCsFixer\Fixer\ConfigurableFixerInterface && null === $this->configuration) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\RequiredFixerConfigurationException($this->getName(), 'Configuration is required.');
        }
        if (0 < $tokens->count() && $this->isCandidate($tokens) && $this->supports($file)) {
            $this->applyFix($file, $tokens);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $nameParts = \explode('\\', static::class);
        $name = \substr(\end($nameParts), 0, -\strlen('Fixer'));
        return \MolliePrefix\PhpCsFixer\Utils::camelCaseToUnderscore($name);
    }
    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    public function supports(\SplFileInfo $file)
    {
        return \true;
    }
    public function configure(array $configuration = null)
    {
        if (!$this instanceof \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface) {
            throw new \LogicException('Cannot configure using Abstract parent, child not implementing "PhpCsFixer\\Fixer\\ConfigurationDefinitionFixerInterface".');
        }
        if (null === $configuration) {
            $message = 'Passing NULL to set default configuration is deprecated and will not be supported in 3.0, use an empty array instead.';
            if (\getenv('PHP_CS_FIXER_FUTURE_MODE')) {
                throw new \InvalidArgumentException("{$message} This check was performed as `PHP_CS_FIXER_FUTURE_MODE` env var is set.");
            }
            @\trigger_error($message, \E_USER_DEPRECATED);
            $configuration = [];
        }
        foreach ($this->getConfigurationDefinition()->getOptions() as $option) {
            if (!$option instanceof \MolliePrefix\PhpCsFixer\FixerConfiguration\DeprecatedFixerOption) {
                continue;
            }
            $name = $option->getName();
            if (\array_key_exists($name, $configuration)) {
                $message = \sprintf('Option "%s" for rule "%s" is deprecated and will be removed in version %d.0. %s', $name, $this->getName(), \MolliePrefix\PhpCsFixer\Console\Application::getMajorVersion() + 1, \str_replace('`', '"', $option->getDeprecationMessage()));
                if (\getenv('PHP_CS_FIXER_FUTURE_MODE')) {
                    throw new \InvalidArgumentException("{$message} This check was performed as `PHP_CS_FIXER_FUTURE_MODE` env var is set.");
                }
                @\trigger_error($message, \E_USER_DEPRECATED);
            }
        }
        try {
            $this->configuration = $this->getConfigurationDefinition()->resolve($configuration);
        } catch (\MolliePrefix\Symfony\Component\OptionsResolver\Exception\MissingOptionsException $exception) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\RequiredFixerConfigurationException($this->getName(), \sprintf('Missing required configuration: %s', $exception->getMessage()), $exception);
        } catch (\MolliePrefix\PhpCsFixer\FixerConfiguration\InvalidOptionsForEnvException $exception) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidForEnvFixerConfigurationException($this->getName(), \sprintf('Invalid configuration for env: %s', $exception->getMessage()), $exception);
        } catch (\MolliePrefix\Symfony\Component\OptionsResolver\Exception\ExceptionInterface $exception) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException($this->getName(), \sprintf('Invalid configuration: %s', $exception->getMessage()), $exception);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getConfigurationDefinition()
    {
        if (!$this instanceof \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface) {
            throw new \LogicException('Cannot get configuration definition using Abstract parent, child not implementing "PhpCsFixer\\Fixer\\ConfigurationDefinitionFixerInterface".');
        }
        if (null === $this->configurationDefinition) {
            $this->configurationDefinition = $this->createConfigurationDefinition();
        }
        return $this->configurationDefinition;
    }
    public function setWhitespacesConfig(\MolliePrefix\PhpCsFixer\WhitespacesFixerConfig $config)
    {
        if (!$this instanceof \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface) {
            throw new \LogicException('Cannot run method for class not implementing "PhpCsFixer\\Fixer\\WhitespacesAwareFixerInterface".');
        }
        $this->whitespacesConfig = $config;
    }
    protected abstract function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens);
    /**
     * @return FixerConfigurationResolverInterface
     */
    protected function createConfigurationDefinition()
    {
        if (!$this instanceof \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface) {
            throw new \LogicException('Cannot create configuration definition using Abstract parent, child not implementing "PhpCsFixer\\Fixer\\ConfigurationDefinitionFixerInterface".');
        }
        throw new \LogicException('Not implemented.');
    }
    private function getDefaultWhitespacesFixerConfig()
    {
        static $defaultWhitespacesFixerConfig = null;
        if (null === $defaultWhitespacesFixerConfig) {
            $defaultWhitespacesFixerConfig = new \MolliePrefix\PhpCsFixer\WhitespacesFixerConfig('    ', "\n");
        }
        return $defaultWhitespacesFixerConfig;
    }
}
