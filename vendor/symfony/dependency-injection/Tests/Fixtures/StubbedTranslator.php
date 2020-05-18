<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures;

use _PhpScoper5ea00cc67502b\Psr\Container\ContainerInterface;
/**
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
class StubbedTranslator
{
    public function __construct(ContainerInterface $container)
    {
    }
    public function addResource($format, $resource, $locale, $domain = null)
    {
    }
}
