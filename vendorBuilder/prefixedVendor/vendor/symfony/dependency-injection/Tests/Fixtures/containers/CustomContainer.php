<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\containers;

use MolliePrefix\Symfony\Component\DependencyInjection\Container;
use MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
class CustomContainer extends \MolliePrefix\Symfony\Component\DependencyInjection\Container
{
    public function getBarService()
    {
    }
    public function getFoobarService()
    {
    }
}
