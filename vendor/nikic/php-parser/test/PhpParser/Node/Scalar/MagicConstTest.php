<?php

namespace MolliePrefix\PhpParser\Node\Scalar;

class MagicConstTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideTestGetName
     */
    public function testGetName(\MolliePrefix\PhpParser\Node\Scalar\MagicConst $magicConst, $name)
    {
        $this->assertSame($name, $magicConst->getName());
    }
    public function provideTestGetName()
    {
        return array(array(new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Class_(), '__CLASS__'), array(new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Dir(), '__DIR__'), array(new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\File(), '__FILE__'), array(new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Function_(), '__FUNCTION__'), array(new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Line(), '__LINE__'), array(new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Method(), '__METHOD__'), array(new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Namespace_(), '__NAMESPACE__'), array(new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Trait_(), '__TRAIT__'));
    }
}
