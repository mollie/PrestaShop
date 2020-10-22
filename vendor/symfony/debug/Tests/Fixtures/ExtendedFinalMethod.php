<?php

namespace MolliePrefix\Symfony\Component\Debug\Tests\Fixtures;

class ExtendedFinalMethod extends \MolliePrefix\Symfony\Component\Debug\Tests\Fixtures\FinalMethod
{
    use FinalMethod2Trait;
    /**
     * {@inheritdoc}
     */
    public function finalMethod()
    {
    }
    public function anotherMethod()
    {
    }
}
