<?php

namespace MolliePrefix\Symfony\Component\Debug\Tests\Fixtures;

trait TraitWithInternalMethod
{
    /**
     * @internal
     */
    public function foo()
    {
    }
}
