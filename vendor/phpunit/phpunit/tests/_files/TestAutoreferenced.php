<?php

namespace MolliePrefix;

use MolliePrefix\PHPUnit\Framework\TestCase;
class TestAutoreferenced extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public $myTestData = null;
    public function testJsonEncodeException($data)
    {
        $this->myTestData = $data;
    }
}
\class_alias('MolliePrefix\\TestAutoreferenced', 'TestAutoreferenced', \false);
