<?php

namespace MolliePrefix;

class ParentClassWithPrivateAttributes
{
    private static $privateStaticParentAttribute = 'foo';
    private $privateParentAttribute = 'bar';
}
\class_alias('MolliePrefix\\ParentClassWithPrivateAttributes', 'ParentClassWithPrivateAttributes', \false);
class ParentClassWithProtectedAttributes extends \MolliePrefix\ParentClassWithPrivateAttributes
{
    protected static $protectedStaticParentAttribute = 'foo';
    protected $protectedParentAttribute = 'bar';
}
\class_alias('MolliePrefix\\ParentClassWithProtectedAttributes', 'ParentClassWithProtectedAttributes', \false);
class ClassWithNonPublicAttributes extends \MolliePrefix\ParentClassWithProtectedAttributes
{
    public static $publicStaticAttribute = 'foo';
    protected static $protectedStaticAttribute = 'bar';
    protected static $privateStaticAttribute = 'baz';
    public $publicAttribute = 'foo';
    public $foo = 1;
    public $bar = 2;
    protected $protectedAttribute = 'bar';
    protected $privateAttribute = 'baz';
    public $publicArray = ['foo'];
    protected $protectedArray = ['bar'];
    protected $privateArray = ['baz'];
}
\class_alias('MolliePrefix\\ClassWithNonPublicAttributes', 'ClassWithNonPublicAttributes', \false);
