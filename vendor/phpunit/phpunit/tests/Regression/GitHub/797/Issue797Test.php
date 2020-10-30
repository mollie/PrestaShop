<?php

namespace MolliePrefix;

class Issue797Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected $preserveGlobalState = \false;
    public function testBootstrapPhpIsExecutedInIsolation()
    {
        $this->assertEquals(\GITHUB_ISSUE, 797);
    }
}
\class_alias('MolliePrefix\\Issue797Test', 'MolliePrefix\\Issue797Test', \false);
