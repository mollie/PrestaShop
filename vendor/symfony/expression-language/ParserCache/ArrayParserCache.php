<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ParserCache;

@trigger_error('The ' . __NAMESPACE__ . '\\ArrayParserCache class is deprecated since Symfony 3.2 and will be removed in 4.0. Use the Symfony\\Component\\Cache\\Adapter\\ArrayAdapter class instead.', E_USER_DEPRECATED);
use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\ParsedExpression;
use function trigger_error;
use const E_USER_DEPRECATED;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 *
 * @deprecated ArrayParserCache class is deprecated since version 3.2 and will be removed in 4.0. Use the Symfony\Component\Cache\Adapter\ArrayAdapter class instead.
 */
class ArrayParserCache implements ParserCacheInterface
{
    private $cache = [];
    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        return isset($this->cache[$key]) ? $this->cache[$key] : null;
    }
    /**
     * {@inheritdoc}
     */
    public function save($key, ParsedExpression $expression)
    {
        $this->cache[$key] = $expression;
    }
}
