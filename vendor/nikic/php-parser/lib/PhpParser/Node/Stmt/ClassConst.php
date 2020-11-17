<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
class ClassConst extends \MolliePrefix\PhpParser\Node\Stmt
{
    /** @var int Modifiers */
    public $flags;
    /** @var Node\Const_[] Constant declarations */
    public $consts;
    /**
     * Constructs a class const list node.
     *
     * @param Node\Const_[] $consts     Constant declarations
     * @param int           $flags      Modifiers
     * @param array         $attributes Additional attributes
     */
    public function __construct(array $consts, $flags = 0, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->flags = $flags;
        $this->consts = $consts;
    }
    public function getSubNodeNames()
    {
        return array('flags', 'consts');
    }
    public function isPublic()
    {
        return ($this->flags & \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC) !== 0 || ($this->flags & \MolliePrefix\PhpParser\Node\Stmt\Class_::VISIBILITY_MODIFIER_MASK) === 0;
    }
    public function isProtected()
    {
        return (bool) ($this->flags & \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED);
    }
    public function isPrivate()
    {
        return (bool) ($this->flags & \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
    }
    public function isStatic()
    {
        return (bool) ($this->flags & \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_STATIC);
    }
}
