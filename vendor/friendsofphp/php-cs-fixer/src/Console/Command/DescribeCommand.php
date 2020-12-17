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
namespace MolliePrefix\PhpCsFixer\Console\Command;

use MolliePrefix\PhpCsFixer\Differ\DiffConsoleFormatter;
use MolliePrefix\PhpCsFixer\Differ\FullDiffer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurableFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\DefinedFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\FixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\AliasedFixerOption;
use MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use MolliePrefix\PhpCsFixer\FixerConfiguration\DeprecatedFixerOption;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSampleInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\FileSpecificCodeSampleInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSampleInterface;
use MolliePrefix\PhpCsFixer\FixerFactory;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\RuleSet\RuleSets;
use MolliePrefix\PhpCsFixer\StdinFileInfo;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Utils;
use MolliePrefix\PhpCsFixer\WordMatcher;
use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter;
use MolliePrefix\Symfony\Component\Console\Input\InputArgument;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\ConsoleOutputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author SpacePossum
 *
 * @internal
 */
final class DescribeCommand extends \MolliePrefix\Symfony\Component\Console\Command\Command
{
    protected static $defaultName = 'describe';
    /**
     * @var string[]
     */
    private $setNames;
    /**
     * @var FixerFactory
     */
    private $fixerFactory;
    /**
     * @var array<string, FixerInterface>
     */
    private $fixers;
    public function __construct(\MolliePrefix\PhpCsFixer\FixerFactory $fixerFactory = null)
    {
        parent::__construct();
        if (null === $fixerFactory) {
            $fixerFactory = new \MolliePrefix\PhpCsFixer\FixerFactory();
            $fixerFactory->registerBuiltInFixers();
        }
        $this->fixerFactory = $fixerFactory;
    }
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Name of rule / set.')])->setDescription('Describe rule / ruleset.');
    }
    /**
     * {@inheritdoc}
     */
    protected function execute(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        if (\MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity() && $output instanceof \MolliePrefix\Symfony\Component\Console\Output\ConsoleOutputInterface) {
            $stdErr = $output->getErrorOutput();
            $stdErr->writeln($this->getApplication()->getLongVersion());
            $stdErr->writeln(\sprintf('Runtime: <info>PHP %s</info>', \PHP_VERSION));
        }
        $name = $input->getArgument('name');
        try {
            if ('@' === $name[0]) {
                $this->describeSet($output, $name);
                return 0;
            }
            $this->describeRule($output, $name);
        } catch (\MolliePrefix\PhpCsFixer\Console\Command\DescribeNameNotFoundException $e) {
            $matcher = new \MolliePrefix\PhpCsFixer\WordMatcher('set' === $e->getType() ? $this->getSetNames() : \array_keys($this->getFixers()));
            $alternative = $matcher->match($name);
            $this->describeList($output, $e->getType());
            throw new \InvalidArgumentException(\sprintf('%s "%s" not found.%s', \ucfirst($e->getType()), $name, null === $alternative ? '' : ' Did you mean "' . $alternative . '"?'));
        }
        return 0;
    }
    /**
     * @param string $name
     */
    private function describeRule(\MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, $name)
    {
        $fixers = $this->getFixers();
        if (!isset($fixers[$name])) {
            throw new \MolliePrefix\PhpCsFixer\Console\Command\DescribeNameNotFoundException($name, 'rule');
        }
        /** @var FixerInterface $fixer */
        $fixer = $fixers[$name];
        if ($fixer instanceof \MolliePrefix\PhpCsFixer\Fixer\DefinedFixerInterface) {
            $definition = $fixer->getDefinition();
        } else {
            $definition = new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Description is not available.', []);
        }
        $description = $definition->getSummary();
        if ($fixer instanceof \MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface) {
            $successors = $fixer->getSuccessorsNames();
            $message = [] === $successors ? 'will be removed on next major version' : \sprintf('use %s instead', \MolliePrefix\PhpCsFixer\Utils::naturalLanguageJoinWithBackticks($successors));
            $message = \MolliePrefix\PhpCsFixer\Preg::replace('/(`.+?`)/', '<info>$1</info>', $message);
            $description .= \sprintf(' <error>DEPRECATED</error>: %s.', $message);
        }
        $output->writeln(\sprintf('<info>Description of</info> %s <info>rule</info>.', $name));
        if ($output->getVerbosity() >= \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(\sprintf('Fixer class: <comment>%s</comment>.', \get_class($fixer)));
        }
        $output->writeln($description);
        if ($definition->getDescription()) {
            $output->writeln($definition->getDescription());
        }
        $output->writeln('');
        if ($fixer->isRisky()) {
            $output->writeln('<error>Fixer applying this rule is risky.</error>');
            if ($definition->getRiskyDescription()) {
                $output->writeln($definition->getRiskyDescription());
            }
            $output->writeln('');
        }
        if ($fixer instanceof \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface) {
            $configurationDefinition = $fixer->getConfigurationDefinition();
            $options = $configurationDefinition->getOptions();
            $output->writeln(\sprintf('Fixer is configurable using following option%s:', 1 === \count($options) ? '' : 's'));
            foreach ($options as $option) {
                $line = '* <info>' . \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter::escape($option->getName()) . '</info>';
                $allowed = \MolliePrefix\PhpCsFixer\Console\Command\HelpCommand::getDisplayableAllowedValues($option);
                if (null !== $allowed) {
                    foreach ($allowed as &$value) {
                        if ($value instanceof \MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset) {
                            $value = 'a subset of <comment>' . \MolliePrefix\PhpCsFixer\Console\Command\HelpCommand::toString($value->getAllowedValues()) . '</comment>';
                        } else {
                            $value = '<comment>' . \MolliePrefix\PhpCsFixer\Console\Command\HelpCommand::toString($value) . '</comment>';
                        }
                    }
                } else {
                    $allowed = \array_map(static function ($type) {
                        return '<comment>' . $type . '</comment>';
                    }, $option->getAllowedTypes());
                }
                if (null !== $allowed) {
                    $line .= ' (' . \implode(', ', $allowed) . ')';
                }
                $description = \MolliePrefix\PhpCsFixer\Preg::replace('/(`.+?`)/', '<info>$1</info>', \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter::escape($option->getDescription()));
                $line .= ': ' . \lcfirst(\MolliePrefix\PhpCsFixer\Preg::replace('/\\.$/', '', $description)) . '; ';
                if ($option->hasDefault()) {
                    $line .= \sprintf('defaults to <comment>%s</comment>', \MolliePrefix\PhpCsFixer\Console\Command\HelpCommand::toString($option->getDefault()));
                } else {
                    $line .= '<comment>required</comment>';
                }
                if ($option instanceof \MolliePrefix\PhpCsFixer\FixerConfiguration\DeprecatedFixerOption) {
                    $line .= '. <error>DEPRECATED</error>: ' . \MolliePrefix\PhpCsFixer\Preg::replace('/(`.+?`)/', '<info>$1</info>', \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter::escape(\lcfirst($option->getDeprecationMessage())));
                }
                if ($option instanceof \MolliePrefix\PhpCsFixer\FixerConfiguration\AliasedFixerOption) {
                    $line .= '; <error>DEPRECATED</error> alias: <comment>' . $option->getAlias() . '</comment>';
                }
                $output->writeln($line);
            }
            $output->writeln('');
        } elseif ($fixer instanceof \MolliePrefix\PhpCsFixer\Fixer\ConfigurableFixerInterface) {
            $output->writeln('<comment>Fixer is configurable.</comment>');
            if ($definition->getConfigurationDescription()) {
                $output->writeln($definition->getConfigurationDescription());
            }
            if ($definition->getDefaultConfiguration()) {
                $output->writeln(\sprintf('Default configuration: <comment>%s</comment>.', \MolliePrefix\PhpCsFixer\Console\Command\HelpCommand::toString($definition->getDefaultConfiguration())));
            }
            $output->writeln('');
        }
        /** @var CodeSampleInterface[] $codeSamples */
        $codeSamples = \array_filter($definition->getCodeSamples(), static function (\MolliePrefix\PhpCsFixer\FixerDefinition\CodeSampleInterface $codeSample) {
            if ($codeSample instanceof \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSampleInterface) {
                return $codeSample->isSuitableFor(\PHP_VERSION_ID);
            }
            return \true;
        });
        if (!\count($codeSamples)) {
            $output->writeln(['Fixing examples can not be demonstrated on the current PHP version.', '']);
        } else {
            $output->writeln('Fixing examples:');
            $differ = new \MolliePrefix\PhpCsFixer\Differ\FullDiffer();
            $diffFormatter = new \MolliePrefix\PhpCsFixer\Differ\DiffConsoleFormatter($output->isDecorated(), \sprintf('<comment>   ---------- begin diff ----------</comment>%s%%s%s<comment>   ----------- end diff -----------</comment>', \PHP_EOL, \PHP_EOL));
            foreach ($codeSamples as $index => $codeSample) {
                $old = $codeSample->getCode();
                $tokens = \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::fromCode($old);
                $configuration = $codeSample->getConfiguration();
                if ($fixer instanceof \MolliePrefix\PhpCsFixer\Fixer\ConfigurableFixerInterface) {
                    $fixer->configure(null === $configuration ? [] : $configuration);
                }
                $file = $codeSample instanceof \MolliePrefix\PhpCsFixer\FixerDefinition\FileSpecificCodeSampleInterface ? $codeSample->getSplFileInfo() : new \MolliePrefix\PhpCsFixer\StdinFileInfo();
                $fixer->fix($file, $tokens);
                $diff = $differ->diff($old, $tokens->generateCode());
                if ($fixer instanceof \MolliePrefix\PhpCsFixer\Fixer\ConfigurableFixerInterface) {
                    if (null === $configuration) {
                        $output->writeln(\sprintf(' * Example #%d. Fixing with the <comment>default</comment> configuration.', $index + 1));
                    } else {
                        $output->writeln(\sprintf(' * Example #%d. Fixing with configuration: <comment>%s</comment>.', $index + 1, \MolliePrefix\PhpCsFixer\Console\Command\HelpCommand::toString($codeSample->getConfiguration())));
                    }
                } else {
                    $output->writeln(\sprintf(' * Example #%d.', $index + 1));
                }
                $output->writeln([$diffFormatter->format($diff, '   %s'), '']);
            }
        }
    }
    /**
     * @param string $name
     */
    private function describeSet(\MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, $name)
    {
        if (!\in_array($name, $this->getSetNames(), \true)) {
            throw new \MolliePrefix\PhpCsFixer\Console\Command\DescribeNameNotFoundException($name, 'set');
        }
        $ruleSetDefinitions = \MolliePrefix\PhpCsFixer\RuleSet\RuleSets::getSetDefinitions();
        $fixers = $this->getFixers();
        $output->writeln(\sprintf('<info>Description of the</info> %s <info>set.</info>', $ruleSetDefinitions[$name]->getName()));
        $output->writeln($this->replaceRstLinks($ruleSetDefinitions[$name]->getDescription()));
        if ($ruleSetDefinitions[$name]->isRisky()) {
            $output->writeln('This set contains <error>risky</error> rules.');
        }
        $output->writeln('');
        $help = '';
        foreach ($ruleSetDefinitions[$name]->getRules() as $rule => $config) {
            if ('@' === $rule[0]) {
                $set = $ruleSetDefinitions[$rule];
                $help .= \sprintf(" * <info>%s</info>%s\n   | %s\n\n", $rule, $set->isRisky() ? ' <error>risky</error>' : '', $this->replaceRstLinks($set->getDescription()));
                continue;
            }
            $fixer = $fixers[$rule];
            if (!$fixer instanceof \MolliePrefix\PhpCsFixer\Fixer\DefinedFixerInterface) {
                throw new \RuntimeException(\sprintf('Cannot describe rule %s, the fixer does not implement "%s".', $rule, \MolliePrefix\PhpCsFixer\Fixer\DefinedFixerInterface::class));
            }
            $definition = $fixer->getDefinition();
            $help .= \sprintf(" * <info>%s</info>%s\n   | %s\n%s\n", $rule, $fixer->isRisky() ? ' <error>risky</error>' : '', $definition->getSummary(), \true !== $config ? \sprintf("   <comment>| Configuration: %s</comment>\n", \MolliePrefix\PhpCsFixer\Console\Command\HelpCommand::toString($config)) : '');
        }
        $output->write($help);
    }
    /**
     * @return array<string, FixerInterface>
     */
    private function getFixers()
    {
        if (null !== $this->fixers) {
            return $this->fixers;
        }
        $fixers = [];
        foreach ($this->fixerFactory->getFixers() as $fixer) {
            $fixers[$fixer->getName()] = $fixer;
        }
        $this->fixers = $fixers;
        \ksort($this->fixers);
        return $this->fixers;
    }
    /**
     * @return string[]
     */
    private function getSetNames()
    {
        if (null !== $this->setNames) {
            return $this->setNames;
        }
        $this->setNames = \MolliePrefix\PhpCsFixer\RuleSet\RuleSets::getSetDefinitionNames();
        return $this->setNames;
    }
    /**
     * @param string $type 'rule'|'set'
     */
    private function describeList(\MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, $type)
    {
        if ($output->getVerbosity() >= \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $describe = ['sets' => $this->getSetNames(), 'rules' => $this->getFixers()];
        } elseif ($output->getVerbosity() >= \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE) {
            $describe = 'set' === $type ? ['sets' => $this->getSetNames()] : ['rules' => $this->getFixers()];
        } else {
            return;
        }
        /** @var string[] $items */
        foreach ($describe as $list => $items) {
            $output->writeln(\sprintf('<comment>Defined %s:</comment>', $list));
            foreach ($items as $name => $item) {
                $output->writeln(\sprintf('* <info>%s</info>', \is_string($name) ? $name : $item));
            }
        }
    }
    /**
     * @param string $content
     *
     * @return string
     */
    private function replaceRstLinks($content)
    {
        return \MolliePrefix\PhpCsFixer\Preg::replaceCallback('/(`[^<]+<[^>]+>`_)/', static function (array $matches) {
            return \MolliePrefix\PhpCsFixer\Preg::replaceCallback('/`(.*)<(.*)>`_/', static function (array $matches) {
                return $matches[1] . '(' . $matches[2] . ')';
            }, $matches[1]);
        }, $content);
    }
}
