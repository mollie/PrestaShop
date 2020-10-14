<?php

namespace MolliePrefix\Prophecy\Doubler\ClassPatch;

use MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode;
use MolliePrefix\Prophecy\Exception\Doubler\ClassCreatorException;
class ThrowablePatch implements \MolliePrefix\Prophecy\Doubler\ClassPatch\ClassPatchInterface
{
    /**
     * Checks if patch supports specific class node.
     *
     * @param ClassNode $node
     * @return bool
     */
    public function supports(\MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode $node)
    {
        return $this->implementsAThrowableInterface($node) && $this->doesNotExtendAThrowableClass($node);
    }
    /**
     * @param ClassNode $node
     * @return bool
     */
    private function implementsAThrowableInterface(\MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode $node)
    {
        foreach ($node->getInterfaces() as $type) {
            if (\is_a($type, 'Throwable', \true)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param ClassNode $node
     * @return bool
     */
    private function doesNotExtendAThrowableClass(\MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode $node)
    {
        return !\is_a($node->getParentClass(), 'Throwable', \true);
    }
    /**
     * Applies patch to the specific class node.
     *
     * @param ClassNode $node
     *
     * @return void
     */
    public function apply(\MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode $node)
    {
        $this->checkItCanBeDoubled($node);
        $this->setParentClassToException($node);
    }
    private function checkItCanBeDoubled(\MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode $node)
    {
        $className = $node->getParentClass();
        if ($className !== 'stdClass') {
            throw new \MolliePrefix\Prophecy\Exception\Doubler\ClassCreatorException(\sprintf('Cannot double concrete class %s as well as implement Traversable', $className), $node);
        }
    }
    private function setParentClassToException(\MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode $node)
    {
        $node->setParentClass('Exception');
        $node->removeMethod('getMessage');
        $node->removeMethod('getCode');
        $node->removeMethod('getFile');
        $node->removeMethod('getLine');
        $node->removeMethod('getTrace');
        $node->removeMethod('getPrevious');
        $node->removeMethod('getNext');
        $node->removeMethod('getTraceAsString');
    }
    /**
     * Returns patch priority, which determines when patch will be applied.
     *
     * @return int Priority number (higher - earlier)
     */
    public function getPriority()
    {
        return 100;
    }
}
