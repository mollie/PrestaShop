<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node;

use _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Compiler;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 */
class GetAttrNode extends \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\Node
{
    const PROPERTY_CALL = 1;
    const METHOD_CALL = 2;
    const ARRAY_CALL = 3;
    public function __construct(\_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\Node $node, \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\Node $attribute, \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ArrayNode $arguments, $type)
    {
        parent::__construct(['node' => $node, 'attribute' => $attribute, 'arguments' => $arguments], ['type' => $type]);
    }
    public function compile(\_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Compiler $compiler)
    {
        switch ($this->attributes['type']) {
            case self::PROPERTY_CALL:
                $compiler->compile($this->nodes['node'])->raw('->')->raw($this->nodes['attribute']->attributes['value']);
                break;
            case self::METHOD_CALL:
                $compiler->compile($this->nodes['node'])->raw('->')->raw($this->nodes['attribute']->attributes['value'])->raw('(')->compile($this->nodes['arguments'])->raw(')');
                break;
            case self::ARRAY_CALL:
                $compiler->compile($this->nodes['node'])->raw('[')->compile($this->nodes['attribute'])->raw(']');
                break;
        }
    }
    public function evaluate($functions, $values)
    {
        switch ($this->attributes['type']) {
            case self::PROPERTY_CALL:
                $obj = $this->nodes['node']->evaluate($functions, $values);
                if (!\is_object($obj)) {
                    throw new \RuntimeException('Unable to get a property on a non-object.');
                }
                $property = $this->nodes['attribute']->attributes['value'];
                return $obj->{$property};
            case self::METHOD_CALL:
                $obj = $this->nodes['node']->evaluate($functions, $values);
                if (!\is_object($obj)) {
                    throw new \RuntimeException('Unable to get a property on a non-object.');
                }
                if (!\is_callable($toCall = [$obj, $this->nodes['attribute']->attributes['value']])) {
                    throw new \RuntimeException(\sprintf('Unable to call method "%s" of object "%s".', $this->nodes['attribute']->attributes['value'], \get_class($obj)));
                }
                return \call_user_func_array($toCall, $this->nodes['arguments']->evaluate($functions, $values));
            case self::ARRAY_CALL:
                $array = $this->nodes['node']->evaluate($functions, $values);
                if (!\is_array($array) && !$array instanceof \ArrayAccess) {
                    throw new \RuntimeException('Unable to get an item on a non-array.');
                }
                return $array[$this->nodes['attribute']->evaluate($functions, $values)];
        }
    }
    public function toArray()
    {
        switch ($this->attributes['type']) {
            case self::PROPERTY_CALL:
                return [$this->nodes['node'], '.', $this->nodes['attribute']];
            case self::METHOD_CALL:
                return [$this->nodes['node'], '.', $this->nodes['attribute'], '(', $this->nodes['arguments'], ')'];
            case self::ARRAY_CALL:
                return [$this->nodes['node'], '[', $this->nodes['attribute'], ']'];
        }
    }
}
