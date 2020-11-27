<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
abstract class ClassLike extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var string|null Name */
    public $name;
    /** @var Node[] Statements */
    public $stmts;
    /**
     * Gets all methods defined directly in this class/interface/trait
     *
     * @return ClassMethod[]
     */
    public function getMethods()
    {
        $methods = array();
        foreach ($this->stmts as $stmt) {
            if ($stmt instanceof \MolliePrefix\PhpParser\Node\Stmt\ClassMethod) {
                $methods[] = $stmt;
            }
        }
        return $methods;
    }
    /**
     * Gets method with the given name defined directly in this class/interface/trait.
     *
     * @param string $name Name of the method (compared case-insensitively)
     *
     * @return ClassMethod|null Method node or null if the method does not exist
     */
    public function getMethod($name)
    {
        $lowerName = \strtolower($name);
        foreach ($this->stmts as $stmt) {
            if ($stmt instanceof \MolliePrefix\PhpParser\Node\Stmt\ClassMethod && $lowerName === \strtolower($stmt->name)) {
                return $stmt;
            }
        }
        return null;
    }
}
