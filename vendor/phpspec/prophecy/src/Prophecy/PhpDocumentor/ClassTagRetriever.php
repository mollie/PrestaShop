<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Prophecy\PhpDocumentor;

use MolliePrefix\phpDocumentor\Reflection\DocBlock\Tags\Method;
use MolliePrefix\phpDocumentor\Reflection\DocBlockFactory;
use MolliePrefix\phpDocumentor\Reflection\Types\ContextFactory;
/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 *
 * @internal
 */
final class ClassTagRetriever implements \MolliePrefix\Prophecy\PhpDocumentor\MethodTagRetrieverInterface
{
    private $docBlockFactory;
    private $contextFactory;
    public function __construct()
    {
        $this->docBlockFactory = \MolliePrefix\phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $this->contextFactory = new \MolliePrefix\phpDocumentor\Reflection\Types\ContextFactory();
    }
    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @return Method[]
     */
    public function getTagList(\ReflectionClass $reflectionClass)
    {
        try {
            $phpdoc = $this->docBlockFactory->create($reflectionClass, $this->contextFactory->createFromReflector($reflectionClass));
            $methods = array();
            foreach ($phpdoc->getTagsByName('method') as $tag) {
                if ($tag instanceof \MolliePrefix\phpDocumentor\Reflection\DocBlock\Tags\Method) {
                    $methods[] = $tag;
                }
            }
            return $methods;
        } catch (\InvalidArgumentException $e) {
            return array();
        }
    }
}
