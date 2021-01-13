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
namespace MolliePrefix\PhpCsFixer\Console;

use MolliePrefix\PhpCsFixer\Cache\CacheManagerInterface;
use MolliePrefix\PhpCsFixer\Cache\Directory;
use MolliePrefix\PhpCsFixer\Cache\DirectoryInterface;
use MolliePrefix\PhpCsFixer\Cache\FileCacheManager;
use MolliePrefix\PhpCsFixer\Cache\FileHandler;
use MolliePrefix\PhpCsFixer\Cache\NullCacheManager;
use MolliePrefix\PhpCsFixer\Cache\Signature;
use MolliePrefix\PhpCsFixer\ConfigInterface;
use MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException;
use MolliePrefix\PhpCsFixer\Differ\DifferInterface;
use MolliePrefix\PhpCsFixer\Differ\NullDiffer;
use MolliePrefix\PhpCsFixer\Differ\SebastianBergmannDiffer;
use MolliePrefix\PhpCsFixer\Differ\UnifiedDiffer;
use MolliePrefix\PhpCsFixer\Finder;
use MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\FixerInterface;
use MolliePrefix\PhpCsFixer\FixerFactory;
use MolliePrefix\PhpCsFixer\Linter\Linter;
use MolliePrefix\PhpCsFixer\Linter\LinterInterface;
use MolliePrefix\PhpCsFixer\Report\ReporterFactory;
use MolliePrefix\PhpCsFixer\Report\ReporterInterface;
use MolliePrefix\PhpCsFixer\RuleSet\RuleSet;
use MolliePrefix\PhpCsFixer\StdinFileInfo;
use MolliePrefix\PhpCsFixer\ToolInfoInterface;
use MolliePrefix\PhpCsFixer\Utils;
use MolliePrefix\PhpCsFixer\WhitespacesFixerConfig;
use MolliePrefix\PhpCsFixer\WordMatcher;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Filesystem\Filesystem;
use MolliePrefix\Symfony\Component\Finder\Finder as SymfonyFinder;
/**
 * The resolver that resolves configuration to use by command line options and config.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Katsuhiro Ogawa <ko.fivestar@gmail.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class ConfigurationResolver
{
    const PATH_MODE_OVERRIDE = 'override';
    const PATH_MODE_INTERSECTION = 'intersection';
    /**
     * @var null|bool
     */
    private $allowRisky;
    /**
     * @var null|ConfigInterface
     */
    private $config;
    /**
     * @var null|string
     */
    private $configFile;
    /**
     * @var string
     */
    private $cwd;
    /**
     * @var ConfigInterface
     */
    private $defaultConfig;
    /**
     * @var null|ReporterInterface
     */
    private $reporter;
    /**
     * @var null|bool
     */
    private $isStdIn;
    /**
     * @var null|bool
     */
    private $isDryRun;
    /**
     * @var null|FixerInterface[]
     */
    private $fixers;
    /**
     * @var null|bool
     */
    private $configFinderIsOverridden;
    /**
     * @var ToolInfoInterface
     */
    private $toolInfo;
    /**
     * @var array
     */
    private $options = ['allow-risky' => null, 'cache-file' => null, 'config' => null, 'diff' => null, 'diff-format' => null, 'dry-run' => null, 'format' => null, 'path' => [], 'path-mode' => self::PATH_MODE_OVERRIDE, 'rules' => null, 'show-progress' => null, 'stop-on-violation' => null, 'using-cache' => null, 'verbosity' => null];
    private $cacheFile;
    private $cacheManager;
    private $differ;
    private $directory;
    private $finder;
    private $format;
    private $linter;
    private $path;
    private $progress;
    private $ruleSet;
    private $usingCache;
    /**
     * @var FixerFactory
     */
    private $fixerFactory;
    /**
     * @param string $cwd
     */
    public function __construct(\MolliePrefix\PhpCsFixer\ConfigInterface $config, array $options, $cwd, \MolliePrefix\PhpCsFixer\ToolInfoInterface $toolInfo)
    {
        $this->cwd = $cwd;
        $this->defaultConfig = $config;
        $this->toolInfo = $toolInfo;
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }
    /**
     * @return null|string
     */
    public function getCacheFile()
    {
        if (!$this->getUsingCache()) {
            return null;
        }
        if (null === $this->cacheFile) {
            if (null === $this->options['cache-file']) {
                $this->cacheFile = $this->getConfig()->getCacheFile();
            } else {
                $this->cacheFile = $this->options['cache-file'];
            }
        }
        return $this->cacheFile;
    }
    /**
     * @return CacheManagerInterface
     */
    public function getCacheManager()
    {
        if (null === $this->cacheManager) {
            $cacheFile = $this->getCacheFile();
            if (null === $cacheFile) {
                $this->cacheManager = new \MolliePrefix\PhpCsFixer\Cache\NullCacheManager();
            } else {
                $this->cacheManager = new \MolliePrefix\PhpCsFixer\Cache\FileCacheManager(new \MolliePrefix\PhpCsFixer\Cache\FileHandler($cacheFile), new \MolliePrefix\PhpCsFixer\Cache\Signature(\PHP_VERSION, $this->toolInfo->getVersion(), $this->getConfig()->getIndent(), $this->getConfig()->getLineEnding(), $this->getRules()), $this->isDryRun(), $this->getDirectory());
            }
        }
        return $this->cacheManager;
    }
    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        if (null === $this->config) {
            foreach ($this->computeConfigFiles() as $configFile) {
                if (!\file_exists($configFile)) {
                    continue;
                }
                $config = self::separatedContextLessInclude($configFile);
                // verify that the config has an instance of Config
                if (!$config instanceof \MolliePrefix\PhpCsFixer\ConfigInterface) {
                    throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('The config file: "%s" does not return a "PhpCsFixer\\ConfigInterface" instance. Got: "%s".', $configFile, \is_object($config) ? \get_class($config) : \gettype($config)));
                }
                $this->config = $config;
                $this->configFile = $configFile;
                break;
            }
            if (null === $this->config) {
                $this->config = $this->defaultConfig;
            }
        }
        return $this->config;
    }
    /**
     * @return null|string
     */
    public function getConfigFile()
    {
        if (null === $this->configFile) {
            $this->getConfig();
        }
        return $this->configFile;
    }
    /**
     * @return DifferInterface
     */
    public function getDiffer()
    {
        if (null === $this->differ) {
            $mapper = ['null' => static function () {
                return new \MolliePrefix\PhpCsFixer\Differ\NullDiffer();
            }, 'sbd' => static function () {
                return new \MolliePrefix\PhpCsFixer\Differ\SebastianBergmannDiffer();
            }, 'udiff' => static function () {
                return new \MolliePrefix\PhpCsFixer\Differ\UnifiedDiffer();
            }];
            if (!$this->options['diff']) {
                $defaultOption = 'null';
            } elseif (\getenv('PHP_CS_FIXER_FUTURE_MODE')) {
                $defaultOption = 'udiff';
            } else {
                $defaultOption = 'sbd';
                // @TODO: 3.0 change to udiff as default
            }
            $option = isset($this->options['diff-format']) ? $this->options['diff-format'] : $defaultOption;
            if (!\is_string($option)) {
                throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('"diff-format" must be a string, "%s" given.', \gettype($option)));
            }
            if (\is_subclass_of($option, \MolliePrefix\PhpCsFixer\Differ\DifferInterface::class)) {
                $this->differ = new $option();
            } elseif (!isset($mapper[$option])) {
                throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('"diff-format" must be any of "%s", got "%s".', \implode('", "', \array_keys($mapper)), $option));
            } else {
                $this->differ = $mapper[$option]();
            }
        }
        return $this->differ;
    }
    /**
     * @return DirectoryInterface
     */
    public function getDirectory()
    {
        if (null === $this->directory) {
            $path = $this->getCacheFile();
            if (null === $path) {
                $absolutePath = $this->cwd;
            } else {
                $filesystem = new \MolliePrefix\Symfony\Component\Filesystem\Filesystem();
                $absolutePath = $filesystem->isAbsolutePath($path) ? $path : $this->cwd . \DIRECTORY_SEPARATOR . $path;
            }
            $this->directory = new \MolliePrefix\PhpCsFixer\Cache\Directory(\dirname($absolutePath));
        }
        return $this->directory;
    }
    /**
     * @return FixerInterface[] An array of FixerInterface
     */
    public function getFixers()
    {
        if (null === $this->fixers) {
            $this->fixers = $this->createFixerFactory()->useRuleSet($this->getRuleSet())->setWhitespacesConfig(new \MolliePrefix\PhpCsFixer\WhitespacesFixerConfig($this->config->getIndent(), $this->config->getLineEnding()))->getFixers();
            if (\false === $this->getRiskyAllowed()) {
                $riskyFixers = \array_map(static function (\MolliePrefix\PhpCsFixer\Fixer\FixerInterface $fixer) {
                    return $fixer->getName();
                }, \array_filter($this->fixers, static function (\MolliePrefix\PhpCsFixer\Fixer\FixerInterface $fixer) {
                    return $fixer->isRisky();
                }));
                if (\count($riskyFixers)) {
                    throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('The rules contain risky fixers (%s), but they are not allowed to run. Perhaps you forget to use --allow-risky=yes option?', \implode('", "', $riskyFixers)));
                }
            }
        }
        return $this->fixers;
    }
    /**
     * @return LinterInterface
     */
    public function getLinter()
    {
        if (null === $this->linter) {
            $this->linter = new \MolliePrefix\PhpCsFixer\Linter\Linter($this->getConfig()->getPhpExecutable());
        }
        return $this->linter;
    }
    /**
     * Returns path.
     *
     * @return string[]
     */
    public function getPath()
    {
        if (null === $this->path) {
            $filesystem = new \MolliePrefix\Symfony\Component\Filesystem\Filesystem();
            $cwd = $this->cwd;
            if (1 === \count($this->options['path']) && '-' === $this->options['path'][0]) {
                $this->path = $this->options['path'];
            } else {
                $this->path = \array_map(static function ($rawPath) use($cwd, $filesystem) {
                    $path = \trim($rawPath);
                    if ('' === $path) {
                        throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException("Invalid path: \"{$rawPath}\".");
                    }
                    $absolutePath = $filesystem->isAbsolutePath($path) ? $path : $cwd . \DIRECTORY_SEPARATOR . $path;
                    if (!\file_exists($absolutePath)) {
                        throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('The path "%s" is not readable.', $path));
                    }
                    return $absolutePath;
                }, $this->options['path']);
            }
        }
        return $this->path;
    }
    /**
     * @throws InvalidConfigurationException
     *
     * @return string
     */
    public function getProgress()
    {
        if (null === $this->progress) {
            if (\MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE <= $this->options['verbosity'] && 'txt' === $this->getFormat()) {
                $progressType = $this->options['show-progress'];
                $progressTypes = ['none', 'run-in', 'estimating', 'estimating-max', 'dots'];
                if (null === $progressType) {
                    $default = 'run-in';
                    if (\getenv('PHP_CS_FIXER_FUTURE_MODE')) {
                        $default = 'dots';
                    }
                    $progressType = $this->getConfig()->getHideProgress() ? 'none' : $default;
                } elseif (!\in_array($progressType, $progressTypes, \true)) {
                    throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('The progress type "%s" is not defined, supported are "%s".', $progressType, \implode('", "', $progressTypes)));
                } elseif (\in_array($progressType, ['estimating', 'estimating-max', 'run-in'], \true)) {
                    $message = 'Passing `estimating`, `estimating-max` or `run-in` is deprecated and will not be supported in 3.0, use `none` or `dots` instead.';
                    if (\getenv('PHP_CS_FIXER_FUTURE_MODE')) {
                        throw new \InvalidArgumentException("{$message} This check was performed as `PHP_CS_FIXER_FUTURE_MODE` env var is set.");
                    }
                    @\trigger_error($message, \E_USER_DEPRECATED);
                }
                $this->progress = $progressType;
            } else {
                $this->progress = 'none';
            }
        }
        return $this->progress;
    }
    /**
     * @return ReporterInterface
     */
    public function getReporter()
    {
        if (null === $this->reporter) {
            $reporterFactory = \MolliePrefix\PhpCsFixer\Report\ReporterFactory::create();
            $reporterFactory->registerBuiltInReporters();
            $format = $this->getFormat();
            try {
                $this->reporter = $reporterFactory->getReporter($format);
            } catch (\UnexpectedValueException $e) {
                $formats = $reporterFactory->getFormats();
                \sort($formats);
                throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('The format "%s" is not defined, supported are "%s".', $format, \implode('", "', $formats)));
            }
        }
        return $this->reporter;
    }
    /**
     * @return bool
     */
    public function getRiskyAllowed()
    {
        if (null === $this->allowRisky) {
            if (null === $this->options['allow-risky']) {
                $this->allowRisky = $this->getConfig()->getRiskyAllowed();
            } else {
                $this->allowRisky = $this->resolveOptionBooleanValue('allow-risky');
            }
        }
        return $this->allowRisky;
    }
    /**
     * Returns rules.
     *
     * @return array
     */
    public function getRules()
    {
        return $this->getRuleSet()->getRules();
    }
    /**
     * @return bool
     */
    public function getUsingCache()
    {
        if (null === $this->usingCache) {
            if (null === $this->options['using-cache']) {
                $this->usingCache = $this->getConfig()->getUsingCache();
            } else {
                $this->usingCache = $this->resolveOptionBooleanValue('using-cache');
            }
        }
        $this->usingCache = $this->usingCache && ($this->toolInfo->isInstalledAsPhar() || $this->toolInfo->isInstalledByComposer());
        return $this->usingCache;
    }
    public function getFinder()
    {
        if (null === $this->finder) {
            $this->finder = $this->resolveFinder();
        }
        return $this->finder;
    }
    /**
     * Returns dry-run flag.
     *
     * @return bool
     */
    public function isDryRun()
    {
        if (null === $this->isDryRun) {
            if ($this->isStdIn()) {
                // Can't write to STDIN
                $this->isDryRun = \true;
            } else {
                $this->isDryRun = $this->options['dry-run'];
            }
        }
        return $this->isDryRun;
    }
    public function shouldStopOnViolation()
    {
        return $this->options['stop-on-violation'];
    }
    /**
     * @return bool
     */
    public function configFinderIsOverridden()
    {
        if (null === $this->configFinderIsOverridden) {
            $this->resolveFinder();
        }
        return $this->configFinderIsOverridden;
    }
    /**
     * Compute file candidates for config file.
     *
     * @return string[]
     */
    private function computeConfigFiles()
    {
        $configFile = $this->options['config'];
        if (null !== $configFile) {
            if (\false === \file_exists($configFile) || \false === \is_readable($configFile)) {
                throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('Cannot read config file "%s".', $configFile));
            }
            return [$configFile];
        }
        $path = $this->getPath();
        if ($this->isStdIn() || 0 === \count($path)) {
            $configDir = $this->cwd;
        } elseif (1 < \count($path)) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException('For multiple paths config parameter is required.');
        } elseif (!\is_file($path[0])) {
            $configDir = $path[0];
        } else {
            $dirName = \pathinfo($path[0], \PATHINFO_DIRNAME);
            $configDir = $dirName ?: $path[0];
        }
        $candidates = [$configDir . \DIRECTORY_SEPARATOR . '.php_cs', $configDir . \DIRECTORY_SEPARATOR . '.php_cs.dist'];
        if ($configDir !== $this->cwd) {
            $candidates[] = $this->cwd . \DIRECTORY_SEPARATOR . '.php_cs';
            $candidates[] = $this->cwd . \DIRECTORY_SEPARATOR . '.php_cs.dist';
        }
        return $candidates;
    }
    /**
     * @return FixerFactory
     */
    private function createFixerFactory()
    {
        if (null === $this->fixerFactory) {
            $fixerFactory = new \MolliePrefix\PhpCsFixer\FixerFactory();
            $fixerFactory->registerBuiltInFixers();
            $fixerFactory->registerCustomFixers($this->getConfig()->getCustomFixers());
            $this->fixerFactory = $fixerFactory;
        }
        return $this->fixerFactory;
    }
    /**
     * @return string
     */
    private function getFormat()
    {
        if (null === $this->format) {
            $this->format = null === $this->options['format'] ? $this->getConfig()->getFormat() : $this->options['format'];
        }
        return $this->format;
    }
    private function getRuleSet()
    {
        if (null === $this->ruleSet) {
            $rules = $this->parseRules();
            $this->validateRules($rules);
            $this->ruleSet = new \MolliePrefix\PhpCsFixer\RuleSet\RuleSet($rules);
        }
        return $this->ruleSet;
    }
    /**
     * @return bool
     */
    private function isStdIn()
    {
        if (null === $this->isStdIn) {
            $this->isStdIn = 1 === \count($this->options['path']) && '-' === $this->options['path'][0];
        }
        return $this->isStdIn;
    }
    /**
     * @param iterable $iterable
     *
     * @return \Traversable
     */
    private function iterableToTraversable($iterable)
    {
        return \is_array($iterable) ? new \ArrayIterator($iterable) : $iterable;
    }
    /**
     * Compute rules.
     *
     * @return array
     */
    private function parseRules()
    {
        if (null === $this->options['rules']) {
            return $this->getConfig()->getRules();
        }
        $rules = \trim($this->options['rules']);
        if ('' === $rules) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException('Empty rules value is not allowed.');
        }
        if ('{' === $rules[0]) {
            $rules = \json_decode($rules, \true);
            if (\JSON_ERROR_NONE !== \json_last_error()) {
                throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('Invalid JSON rules input: "%s".', \json_last_error_msg()));
            }
            return $rules;
        }
        $rules = [];
        foreach (\explode(',', $this->options['rules']) as $rule) {
            $rule = \trim($rule);
            if ('' === $rule) {
                throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException('Empty rule name is not allowed.');
            }
            if ('-' === $rule[0]) {
                $rules[\substr($rule, 1)] = \false;
            } else {
                $rules[$rule] = \true;
            }
        }
        return $rules;
    }
    /**
     * @throws InvalidConfigurationException
     */
    private function validateRules(array $rules)
    {
        /**
         * Create a ruleset that contains all configured rules, even when they originally have been disabled.
         *
         * @see RuleSet::resolveSet()
         */
        $ruleSet = [];
        foreach ($rules as $key => $value) {
            if (\is_int($key)) {
                throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('Missing value for "%s" rule/set.', $value));
            }
            $ruleSet[$key] = \true;
        }
        $ruleSet = new \MolliePrefix\PhpCsFixer\RuleSet\RuleSet($ruleSet);
        /** @var string[] $configuredFixers */
        $configuredFixers = \array_keys($ruleSet->getRules());
        $fixers = $this->createFixerFactory()->getFixers();
        /** @var string[] $availableFixers */
        $availableFixers = \array_map(static function (\MolliePrefix\PhpCsFixer\Fixer\FixerInterface $fixer) {
            return $fixer->getName();
        }, $fixers);
        $unknownFixers = \array_diff($configuredFixers, $availableFixers);
        if (\count($unknownFixers)) {
            $matcher = new \MolliePrefix\PhpCsFixer\WordMatcher($availableFixers);
            $message = 'The rules contain unknown fixers: ';
            foreach ($unknownFixers as $unknownFixer) {
                $alternative = $matcher->match($unknownFixer);
                $message .= \sprintf('"%s"%s, ', $unknownFixer, null === $alternative ? '' : ' (did you mean "' . $alternative . '"?)');
            }
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\substr($message, 0, -2) . '.');
        }
        foreach ($fixers as $fixer) {
            $fixerName = $fixer->getName();
            if (isset($rules[$fixerName]) && $fixer instanceof \MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface) {
                $successors = $fixer->getSuccessorsNames();
                $messageEnd = [] === $successors ? \sprintf(' and will be removed in version %d.0.', \MolliePrefix\PhpCsFixer\Console\Application::getMajorVersion() + 1) : \sprintf('. Use %s instead.', \str_replace('`', '"', \MolliePrefix\PhpCsFixer\Utils::naturalLanguageJoinWithBackticks($successors)));
                $message = "Rule \"{$fixerName}\" is deprecated{$messageEnd}";
                if (\getenv('PHP_CS_FIXER_FUTURE_MODE')) {
                    throw new \RuntimeException("{$message} This check was performed as `PHP_CS_FIXER_FUTURE_MODE` env var is set.");
                }
                @\trigger_error($message, \E_USER_DEPRECATED);
            }
        }
    }
    /**
     * Apply path on config instance.
     */
    private function resolveFinder()
    {
        $this->configFinderIsOverridden = \false;
        if ($this->isStdIn()) {
            return new \ArrayIterator([new \MolliePrefix\PhpCsFixer\StdinFileInfo()]);
        }
        $modes = [self::PATH_MODE_OVERRIDE, self::PATH_MODE_INTERSECTION];
        if (!\in_array($this->options['path-mode'], $modes, \true)) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('The path-mode "%s" is not defined, supported are "%s".', $this->options['path-mode'], \implode('", "', $modes)));
        }
        $isIntersectionPathMode = self::PATH_MODE_INTERSECTION === $this->options['path-mode'];
        $paths = \array_filter(\array_map(static function ($path) {
            return \realpath($path);
        }, $this->getPath()));
        if (!\count($paths)) {
            if ($isIntersectionPathMode) {
                return new \ArrayIterator([]);
            }
            return $this->iterableToTraversable($this->getConfig()->getFinder());
        }
        $pathsByType = ['file' => [], 'dir' => []];
        foreach ($paths as $path) {
            if (\is_file($path)) {
                $pathsByType['file'][] = $path;
            } else {
                $pathsByType['dir'][] = $path . \DIRECTORY_SEPARATOR;
            }
        }
        $nestedFinder = null;
        $currentFinder = $this->iterableToTraversable($this->getConfig()->getFinder());
        try {
            $nestedFinder = $currentFinder instanceof \IteratorAggregate ? $currentFinder->getIterator() : $currentFinder;
        } catch (\Exception $e) {
        }
        if ($isIntersectionPathMode) {
            if (null === $nestedFinder) {
                throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException('Cannot create intersection with not-fully defined Finder in configuration file.');
            }
            return new \CallbackFilterIterator(new \IteratorIterator($nestedFinder), static function (\SplFileInfo $current) use($pathsByType) {
                $currentRealPath = $current->getRealPath();
                if (\in_array($currentRealPath, $pathsByType['file'], \true)) {
                    return \true;
                }
                foreach ($pathsByType['dir'] as $path) {
                    if (0 === \strpos($currentRealPath, $path)) {
                        return \true;
                    }
                }
                return \false;
            });
        }
        if (null !== $this->getConfigFile() && null !== $nestedFinder) {
            $this->configFinderIsOverridden = \true;
        }
        if ($currentFinder instanceof \MolliePrefix\Symfony\Component\Finder\Finder && null === $nestedFinder) {
            // finder from configuration Symfony finder and it is not fully defined, we may fulfill it
            return $currentFinder->in($pathsByType['dir'])->append($pathsByType['file']);
        }
        return \MolliePrefix\PhpCsFixer\Finder::create()->in($pathsByType['dir'])->append($pathsByType['file']);
    }
    /**
     * Set option that will be resolved.
     *
     * @param string $name
     * @param mixed  $value
     */
    private function setOption($name, $value)
    {
        if (!\array_key_exists($name, $this->options)) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('Unknown option name: "%s".', $name));
        }
        $this->options[$name] = $value;
    }
    /**
     * @param string $optionName
     *
     * @return bool
     */
    private function resolveOptionBooleanValue($optionName)
    {
        $value = $this->options[$optionName];
        if (\is_bool($value)) {
            return $value;
        }
        if (!\is_string($value)) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException(\sprintf('Expected boolean or string value for option "%s".', $optionName));
        }
        if ('yes' === $value) {
            return \true;
        }
        if ('no' === $value) {
            return \false;
        }
        $message = \sprintf('Expected "yes" or "no" for option "%s", other values are deprecated and support will be removed in 3.0. Got "%s", this implicitly set the option to "false".', $optionName, $value);
        if (\getenv('PHP_CS_FIXER_FUTURE_MODE')) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException("{$message} This check was performed as `PHP_CS_FIXER_FUTURE_MODE` env var is set.");
        }
        @\trigger_error($message, \E_USER_DEPRECATED);
        return \false;
    }
    private static function separatedContextLessInclude($path)
    {
        return include $path;
    }
}
