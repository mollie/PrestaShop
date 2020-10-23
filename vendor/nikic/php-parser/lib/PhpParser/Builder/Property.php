<?php

namespace MolliePrefix\PhpParser\Builder;

use MolliePrefix\PhpParser;
use MolliePrefix\PhpParser\Node\Stmt;
class Property extends \MolliePrefix\PhpParser\BuilderAbstract
{
    protected $name;
    protected $flags = 0;
    protected $default = null;
    protected $attributes = array();
    /**
     * Creates a property builder.
     *
     * @param string $name Name of the property
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
    /**
     * Makes the property public.
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makePublic()
    {
        $this->setModifier(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC);
        return $this;
    }
    /**
     * Makes the property protected.
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeProtected()
    {
        $this->setModifier(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED);
        return $this;
    }
    /**
     * Makes the property private.
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makePrivate()
    {
        $this->setModifier(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        return $this;
    }
    /**
     * Makes the property static.
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeStatic()
    {
        $this->setModifier(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_STATIC);
        return $this;
    }
    /**
     * Sets default value for the property.
     *
     * @param mixed $value Default value to use
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function setDefault($value)
    {
        $this->default = $this->normalizeValue($value);
        return $this;
    }
    /**
     * Sets doc comment for the property.
     *
     * @param PhpParser\Comment\Doc|string $docComment Doc comment to set
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function setDocComment($docComment)
    {
        $this->attributes = array('comments' => array($this->normalizeDocComment($docComment)));
        return $this;
    }
    /**
     * Returns the built class node.
     *
     * @return Stmt\Property The built property node
     */
    public function getNode()
    {
        return new \MolliePrefix\PhpParser\Node\Stmt\Property($this->flags !== 0 ? $this->flags : \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC, array(new \MolliePrefix\PhpParser\Node\Stmt\PropertyProperty($this->name, $this->default)), $this->attributes);
    }
}
