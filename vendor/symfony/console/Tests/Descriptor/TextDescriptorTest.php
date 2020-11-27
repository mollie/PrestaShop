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

use MolliePrefix\Symfony\Component\Console\Descriptor\TextDescriptor;
use MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorApplication2;
use MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorApplicationMbString;
use MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorCommandMbString;
class TextDescriptorTest extends \MolliePrefix\Symfony\Component\Console\Tests\Descriptor\AbstractDescriptorTest
{
    public function getDescribeCommandTestData()
    {
        return $this->getDescriptionTestData(\array_merge(\MolliePrefix\Symfony\Component\Console\Tests\Descriptor\ObjectsProvider::getCommands(), ['command_mbstring' => new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorCommandMbString()]));
    }
    public function getDescribeApplicationTestData()
    {
        return $this->getDescriptionTestData(\array_merge(\MolliePrefix\Symfony\Component\Console\Tests\Descriptor\ObjectsProvider::getApplications(), ['application_mbstring' => new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorApplicationMbString()]));
    }
    public function testDescribeApplicationWithFilteredNamespace()
    {
        $application = new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DescriptorApplication2();
        $this->assertDescription(\file_get_contents(__DIR__ . '/../Fixtures/application_filtered_namespace.txt'), $application, ['namespace' => 'command4']);
    }
    protected function getDescriptor()
    {
        return new \MolliePrefix\Symfony\Component\Console\Descriptor\TextDescriptor();
    }
    protected function getFormat()
    {
        return 'txt';
    }
}
