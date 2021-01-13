<?php

namespace MolliePrefix\PhpParser\NodeVisitor;

use MolliePrefix\PhpParser\Error;
use MolliePrefix\PhpParser\ErrorHandler;
use MolliePrefix\PhpParser\Node;
use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Name;
use MolliePrefix\PhpParser\Node\Name\FullyQualified;
use MolliePrefix\PhpParser\Node\Stmt;
use MolliePrefix\PhpParser\NodeVisitorAbstract;
class NameResolver extends \MolliePrefix\PhpParser\NodeVisitorAbstract
{
    /** @var null|Name Current namespace */
    protected $namespace;
    /** @var array Map of format [aliasType => [aliasName => originalName]] */
    protected $aliases;
    /** @var ErrorHandler Error handler */
    protected $errorHandler;
    /** @var bool Whether to preserve original names */
    protected $preserveOriginalNames;
    /**
     * Constructs a name resolution visitor.
     *
     * Options: If "preserveOriginalNames" is enabled, an "originalName" attribute will be added to
     * all name nodes that underwent resolution.
     *
     * @param ErrorHandler|null $errorHandler Error handler
     * @param array $options Options
     */
    public function __construct(\MolliePrefix\PhpParser\ErrorHandler $errorHandler = null, array $options = [])
    {
        $this->errorHandler = $errorHandler ?: new \MolliePrefix\PhpParser\ErrorHandler\Throwing();
        $this->preserveOriginalNames = !empty($options['preserveOriginalNames']);
    }
    public function beforeTraverse(array $nodes)
    {
        $this->resetState();
    }
    public function enterNode(\MolliePrefix\PhpParser\Node $node)
    {
        if ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\Namespace_) {
            $this->resetState($node->name);
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                $this->addAlias($use, $node->type, null);
            }
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\GroupUse) {
            foreach ($node->uses as $use) {
                $this->addAlias($use, $node->type, $node->prefix);
            }
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\Class_) {
            if (null !== $node->extends) {
                $node->extends = $this->resolveClassName($node->extends);
            }
            foreach ($node->implements as &$interface) {
                $interface = $this->resolveClassName($interface);
            }
            if (null !== $node->name) {
                $this->addNamespacedName($node);
            }
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\Interface_) {
            foreach ($node->extends as &$interface) {
                $interface = $this->resolveClassName($interface);
            }
            $this->addNamespacedName($node);
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\Trait_) {
            $this->addNamespacedName($node);
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\Function_) {
            $this->addNamespacedName($node);
            $this->resolveSignature($node);
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\ClassMethod || $node instanceof \MolliePrefix\PhpParser\Node\Expr\Closure) {
            $this->resolveSignature($node);
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\Const_) {
            foreach ($node->consts as $const) {
                $this->addNamespacedName($const);
            }
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Expr\StaticCall || $node instanceof \MolliePrefix\PhpParser\Node\Expr\StaticPropertyFetch || $node instanceof \MolliePrefix\PhpParser\Node\Expr\ClassConstFetch || $node instanceof \MolliePrefix\PhpParser\Node\Expr\New_ || $node instanceof \MolliePrefix\PhpParser\Node\Expr\Instanceof_) {
            if ($node->class instanceof \MolliePrefix\PhpParser\Node\Name) {
                $node->class = $this->resolveClassName($node->class);
            }
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\Catch_) {
            foreach ($node->types as &$type) {
                $type = $this->resolveClassName($type);
            }
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Expr\FuncCall) {
            if ($node->name instanceof \MolliePrefix\PhpParser\Node\Name) {
                $node->name = $this->resolveOtherName($node->name, \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_FUNCTION);
            }
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Expr\ConstFetch) {
            $node->name = $this->resolveOtherName($node->name, \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_CONSTANT);
        } elseif ($node instanceof \MolliePrefix\PhpParser\Node\Stmt\TraitUse) {
            foreach ($node->traits as &$trait) {
                $trait = $this->resolveClassName($trait);
            }
            foreach ($node->adaptations as $adaptation) {
                if (null !== $adaptation->trait) {
                    $adaptation->trait = $this->resolveClassName($adaptation->trait);
                }
                if ($adaptation instanceof \MolliePrefix\PhpParser\Node\Stmt\TraitUseAdaptation\Precedence) {
                    foreach ($adaptation->insteadof as &$insteadof) {
                        $insteadof = $this->resolveClassName($insteadof);
                    }
                }
            }
        }
    }
    protected function resetState(\MolliePrefix\PhpParser\Node\Name $namespace = null)
    {
        $this->namespace = $namespace;
        $this->aliases = array(\MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_NORMAL => array(), \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_FUNCTION => array(), \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_CONSTANT => array());
    }
    protected function addAlias(\MolliePrefix\PhpParser\Node\Stmt\UseUse $use, $type, \MolliePrefix\PhpParser\Node\Name $prefix = null)
    {
        // Add prefix for group uses
        $name = $prefix ? \MolliePrefix\PhpParser\Node\Name::concat($prefix, $use->name) : $use->name;
        // Type is determined either by individual element or whole use declaration
        $type |= $use->type;
        // Constant names are case sensitive, everything else case insensitive
        if ($type === \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_CONSTANT) {
            $aliasName = $use->alias;
        } else {
            $aliasName = \strtolower($use->alias);
        }
        if (isset($this->aliases[$type][$aliasName])) {
            $typeStringMap = array(\MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_NORMAL => '', \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_FUNCTION => 'function ', \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_CONSTANT => 'const ');
            $this->errorHandler->handleError(new \MolliePrefix\PhpParser\Error(\sprintf('Cannot use %s%s as %s because the name is already in use', $typeStringMap[$type], $name, $use->alias), $use->getAttributes()));
            return;
        }
        $this->aliases[$type][$aliasName] = $name;
    }
    /** @param Stmt\Function_|Stmt\ClassMethod|Expr\Closure $node */
    private function resolveSignature($node)
    {
        foreach ($node->params as $param) {
            $param->type = $this->resolveType($param->type);
        }
        $node->returnType = $this->resolveType($node->returnType);
    }
    private function resolveType($node)
    {
        if ($node instanceof \MolliePrefix\PhpParser\Node\NullableType) {
            $node->type = $this->resolveType($node->type);
            return $node;
        }
        if ($node instanceof \MolliePrefix\PhpParser\Node\Name) {
            return $this->resolveClassName($node);
        }
        return $node;
    }
    protected function resolveClassName(\MolliePrefix\PhpParser\Node\Name $name)
    {
        if ($this->preserveOriginalNames) {
            // Save the original name
            $originalName = $name;
            $name = clone $originalName;
            $name->setAttribute('originalName', $originalName);
        }
        // don't resolve special class names
        if (\in_array(\strtolower($name->toString()), array('self', 'parent', 'static'))) {
            if (!$name->isUnqualified()) {
                $this->errorHandler->handleError(new \MolliePrefix\PhpParser\Error(\sprintf("'\\%s' is an invalid class name", $name->toString()), $name->getAttributes()));
            }
            return $name;
        }
        // fully qualified names are already resolved
        if ($name->isFullyQualified()) {
            return $name;
        }
        $aliasName = \strtolower($name->getFirst());
        if (!$name->isRelative() && isset($this->aliases[\MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_NORMAL][$aliasName])) {
            // resolve aliases (for non-relative names)
            $alias = $this->aliases[\MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_NORMAL][$aliasName];
            return \MolliePrefix\PhpParser\Node\Name\FullyQualified::concat($alias, $name->slice(1), $name->getAttributes());
        }
        // if no alias exists prepend current namespace
        return \MolliePrefix\PhpParser\Node\Name\FullyQualified::concat($this->namespace, $name, $name->getAttributes());
    }
    protected function resolveOtherName(\MolliePrefix\PhpParser\Node\Name $name, $type)
    {
        if ($this->preserveOriginalNames) {
            // Save the original name
            $originalName = $name;
            $name = clone $originalName;
            $name->setAttribute('originalName', $originalName);
        }
        // fully qualified names are already resolved
        if ($name->isFullyQualified()) {
            return $name;
        }
        // resolve aliases for qualified names
        $aliasName = \strtolower($name->getFirst());
        if ($name->isQualified() && isset($this->aliases[\MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_NORMAL][$aliasName])) {
            $alias = $this->aliases[\MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_NORMAL][$aliasName];
            return \MolliePrefix\PhpParser\Node\Name\FullyQualified::concat($alias, $name->slice(1), $name->getAttributes());
        }
        if ($name->isUnqualified()) {
            if ($type === \MolliePrefix\PhpParser\Node\Stmt\Use_::TYPE_CONSTANT) {
                // constant aliases are case-sensitive, function aliases case-insensitive
                $aliasName = $name->getFirst();
            }
            if (isset($this->aliases[$type][$aliasName])) {
                // resolve unqualified aliases
                return new \MolliePrefix\PhpParser\Node\Name\FullyQualified($this->aliases[$type][$aliasName], $name->getAttributes());
            }
            if (null === $this->namespace) {
                // outside of a namespace unaliased unqualified is same as fully qualified
                return new \MolliePrefix\PhpParser\Node\Name\FullyQualified($name, $name->getAttributes());
            }
            // unqualified names inside a namespace cannot be resolved at compile-time
            // add the namespaced version of the name as an attribute
            $name->setAttribute('namespacedName', \MolliePrefix\PhpParser\Node\Name\FullyQualified::concat($this->namespace, $name, $name->getAttributes()));
            return $name;
        }
        // if no alias exists prepend current namespace
        return \MolliePrefix\PhpParser\Node\Name\FullyQualified::concat($this->namespace, $name, $name->getAttributes());
    }
    protected function addNamespacedName(\MolliePrefix\PhpParser\Node $node)
    {
        $node->namespacedName = \MolliePrefix\PhpParser\Node\Name::concat($this->namespace, $node->name);
    }
}
