<?php

namespace MolliePrefix\PhpParser;

/**
 * @codeCoverageIgnore
 */
class NodeVisitorAbstract implements \MolliePrefix\PhpParser\NodeVisitor
{
    public function beforeTraverse(array $nodes)
    {
    }
    public function enterNode(\MolliePrefix\PhpParser\Node $node)
    {
    }
    public function leaveNode(\MolliePrefix\PhpParser\Node $node)
    {
    }
    public function afterTraverse(array $nodes)
    {
    }
}
