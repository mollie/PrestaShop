<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage;

use _PhpScoper5ece82d7231e4\Psr\Cache\CacheItemPoolInterface;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\ArrayAdapter;
use _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter;
use _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;
/**
 * Allows to compile and evaluate expressions written in your own DSL.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExpressionLanguage
{
    private $cache;
    private $lexer;
    private $parser;
    private $compiler;
    protected $functions = [];
    /**
     * @param CacheItemPoolInterface                $cache
     * @param ExpressionFunctionProviderInterface[] $providers
     */
    public function __construct($cache = null, array $providers = [])
    {
        if (null !== $cache) {
            if ($cache instanceof \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface) {
                @\trigger_error(\sprintf('Passing an instance of %s as constructor argument for %s is deprecated as of 3.2 and will be removed in 4.0. Pass an instance of %s instead.', \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface::class, self::class, \_PhpScoper5ece82d7231e4\Psr\Cache\CacheItemPoolInterface::class), \E_USER_DEPRECATED);
                $cache = new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter($cache);
            } elseif (!$cache instanceof \_PhpScoper5ece82d7231e4\Psr\Cache\CacheItemPoolInterface) {
                throw new \InvalidArgumentException(\sprintf('Cache argument has to implement "%s".', \_PhpScoper5ece82d7231e4\Psr\Cache\CacheItemPoolInterface::class));
            }
        }
        $this->cache = $cache ?: new \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\ArrayAdapter();
        $this->registerFunctions();
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }
    }
    /**
     * Compiles an expression source code.
     *
     * @param Expression|string $expression The expression to compile
     * @param array             $names      An array of valid names
     *
     * @return string The compiled PHP source code
     */
    public function compile($expression, $names = [])
    {
        return $this->getCompiler()->compile($this->parse($expression, $names)->getNodes())->getSource();
    }
    /**
     * Evaluate an expression.
     *
     * @param Expression|string $expression The expression to compile
     * @param array             $values     An array of values
     *
     * @return mixed The result of the evaluation of the expression
     */
    public function evaluate($expression, $values = [])
    {
        return $this->parse($expression, \array_keys($values))->getNodes()->evaluate($this->functions, $values);
    }
    /**
     * Parses an expression.
     *
     * @param Expression|string $expression The expression to parse
     * @param array             $names      An array of valid names
     *
     * @return ParsedExpression A ParsedExpression instance
     */
    public function parse($expression, $names)
    {
        if ($expression instanceof \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\ParsedExpression) {
            return $expression;
        }
        \asort($names);
        $cacheKeyItems = [];
        foreach ($names as $nameKey => $name) {
            $cacheKeyItems[] = \is_int($nameKey) ? $name : $nameKey . ':' . $name;
        }
        $cacheItem = $this->cache->getItem(\rawurlencode($expression . '//' . \implode('|', $cacheKeyItems)));
        if (null === ($parsedExpression = $cacheItem->get())) {
            $nodes = $this->getParser()->parse($this->getLexer()->tokenize((string) $expression), $names);
            $parsedExpression = new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\ParsedExpression((string) $expression, $nodes);
            $cacheItem->set($parsedExpression);
            $this->cache->save($cacheItem);
        }
        return $parsedExpression;
    }
    /**
     * Registers a function.
     *
     * @param string   $name      The function name
     * @param callable $compiler  A callable able to compile the function
     * @param callable $evaluator A callable able to evaluate the function
     *
     * @throws \LogicException when registering a function after calling evaluate(), compile() or parse()
     *
     * @see ExpressionFunction
     */
    public function register($name, callable $compiler, callable $evaluator)
    {
        if (null !== $this->parser) {
            throw new \LogicException('Registering functions after calling evaluate(), compile() or parse() is not supported.');
        }
        $this->functions[$name] = ['compiler' => $compiler, 'evaluator' => $evaluator];
    }
    public function addFunction(\_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\ExpressionFunction $function)
    {
        $this->register($function->getName(), $function->getCompiler(), $function->getEvaluator());
    }
    public function registerProvider(\_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface $provider)
    {
        foreach ($provider->getFunctions() as $function) {
            $this->addFunction($function);
        }
    }
    protected function registerFunctions()
    {
        $this->addFunction(\_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\ExpressionFunction::fromPhp('constant'));
    }
    private function getLexer()
    {
        if (null === $this->lexer) {
            $this->lexer = new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Lexer();
        }
        return $this->lexer;
    }
    private function getParser()
    {
        if (null === $this->parser) {
            $this->parser = new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Parser($this->functions);
        }
        return $this->parser;
    }
    private function getCompiler()
    {
        if (null === $this->compiler) {
            $this->compiler = new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Compiler($this->functions);
        }
        return $this->compiler->reset();
    }
}
