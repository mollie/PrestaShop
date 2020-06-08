<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\Config\Tests\Definition\Builder;

use _PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase;
use _PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
class BooleanNodeDefinitionTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    public function testCannotBeEmptyThrowsAnException()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\Config\\Definition\\Exception\\InvalidDefinitionException');
        $this->expectExceptionMessage('->cannotBeEmpty() is not applicable to BooleanNodeDefinition.');
        $def = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition('foo');
        $def->cannotBeEmpty();
    }
    public function testSetDeprecated()
    {
        $def = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition('foo');
        $def->setDeprecated('The "%path%" node is deprecated.');
        $node = $def->getNode();
        $this->assertTrue($node->isDeprecated());
        $this->assertSame('The "foo" node is deprecated.', $node->getDeprecationMessage($node->getName(), $node->getPath()));
    }
}
