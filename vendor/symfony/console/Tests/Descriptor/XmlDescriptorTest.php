<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Descriptor;

use MolliePrefix\Symfony\Component\Console\Descriptor\XmlDescriptor;
class XmlDescriptorTest extends \MolliePrefix\Symfony\Component\Console\Tests\Descriptor\AbstractDescriptorTest
{
    protected function getDescriptor()
    {
        return new \MolliePrefix\Symfony\Component\Console\Descriptor\XmlDescriptor();
    }
    protected function getFormat()
    {
        return 'xml';
    }
}
