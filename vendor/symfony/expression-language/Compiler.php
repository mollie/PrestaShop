<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage;

use _PhpScoper5ea00cc67502b\Symfony\Component\ExpressionLanguage\Node\Node;
use function addcslashes;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function setlocale;
use function sprintf;
use const LC_NUMERIC;

/**
 * Compiles a node to PHP code.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Compiler
{
    private $source;
    private $functions;
    public function __construct(array $functions)
    {
        $this->functions = $functions;
    }
    public function getFunction($name)
    {
        return $this->functions[$name];
    }
    /**
     * Gets the current PHP code after compilation.
     *
     * @return string The PHP code
     */
    public function getSource()
    {
        return $this->source;
    }
    public function reset()
    {
        $this->source = '';
        return $this;
    }
    /**
     * Compiles a node.
     *
     * @return $this
     */
    public function compile(Node $node)
    {
        $node->compile($this);
        return $this;
    }
    public function subcompile(Node $node)
    {
        $current = $this->source;
        $this->source = '';
        $node->compile($this);
        $source = $this->source;
        $this->source = $current;
        return $source;
    }
    /**
     * Adds a raw string to the compiled code.
     *
     * @param string $string The string
     *
     * @return $this
     */
    public function raw($string)
    {
        $this->source .= $string;
        return $this;
    }
    /**
     * Adds a quoted string to the compiled code.
     *
     * @param string $value The string
     *
     * @return $this
     */
    public function string($value)
    {
        $this->source .= sprintf('"%s"', addcslashes($value, "\0\t\"\$\\"));
        return $this;
    }
    /**
     * Returns a PHP representation of a given value.
     *
     * @param mixed $value The value to convert
     *
     * @return $this
     */
    public function repr($value)
    {
        if (is_int($value) || is_float($value)) {
            if (false !== ($locale = setlocale(LC_NUMERIC, 0))) {
                setlocale(LC_NUMERIC, 'C');
            }
            $this->raw($value);
            if (false !== $locale) {
                setlocale(LC_NUMERIC, $locale);
            }
        } elseif (null === $value) {
            $this->raw('null');
        } elseif (is_bool($value)) {
            $this->raw($value ? 'true' : 'false');
        } elseif (is_array($value)) {
            $this->raw('[');
            $first = true;
            foreach ($value as $key => $value) {
                if (!$first) {
                    $this->raw(', ');
                }
                $first = false;
                $this->repr($key);
                $this->raw(' => ');
                $this->repr($value);
            }
            $this->raw(']');
        } else {
            $this->string($value);
        }
        return $this;
    }
}
