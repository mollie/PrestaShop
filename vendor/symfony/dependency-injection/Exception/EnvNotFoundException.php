<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception;

/**
 * This exception is thrown when an environment variable is not found.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class EnvNotFoundException extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
{
    public function __construct($name)
    {
        parent::__construct(\sprintf('Environment variable not found: "%s".', $name));
    }
}
