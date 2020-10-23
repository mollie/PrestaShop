<?php

namespace MolliePrefix;

function foo($a, array $b, array $c = array())
{
}
interface i
{
    public function m($a, array $b, array $c = array());
}
\class_alias('MolliePrefix\\i', 'i', \false);
abstract class a
{
    public abstract function m($a, array $b, array $c = array());
}
\class_alias('MolliePrefix\\a', 'a', \false);
class c
{
    public function m($a, array $b, array $c = array())
    {
    }
}
\class_alias('MolliePrefix\\c', 'c', \false);
