<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Prophecy\Doubler\ClassPatch;

use MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode;
/**
 * Exception patch for HHVM to remove the stubs from special methods
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class HhvmExceptionPatch implements \MolliePrefix\Prophecy\Doubler\ClassPatch\ClassPatchInterface
{
    /**
     * Supports exceptions on HHVM.
     *
     * @param ClassNode $node
     *
     * @return bool
     */
    public function supports(\MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode $node)
    {
        if (!\defined('HHVM_VERSION')) {
            return \false;
        }
        return 'Exception' === $node->getParentClass() || \is_subclass_of($node->getParentClass(), 'Exception');
    }
    /**
     * Removes special exception static methods from the doubled methods.
     *
     * @param ClassNode $node
     *
     * @return void
     */
    public function apply(\MolliePrefix\Prophecy\Doubler\Generator\Node\ClassNode $node)
    {
        if ($node->hasMethod('setTraceOptions')) {
            $node->getMethod('setTraceOptions')->useParentCode();
        }
        if ($node->hasMethod('getTraceOptions')) {
            $node->getMethod('getTraceOptions')->useParentCode();
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return -50;
    }
}
