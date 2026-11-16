<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Tests\Unit\Adapter;

use Mollie\Adapter\ToolsAdapter;
use Mollie\Tests\Unit\BaseTestCase;

class ToolsAdapterTest extends BaseTestCase
{
    public function testDisplayDateKeepsTimeWhenFullIsRequested(): void
    {
        $result = (new ToolsAdapter())->displayDate('2026-11-03 14:35:12', true);

        $this->assertStringContainsString('14:35', $result);
    }

    public function testDisplayDateOmitsTimeByDefault(): void
    {
        $result = (new ToolsAdapter())->displayDate('2026-11-03 14:35:12');

        $this->assertStringNotContainsString('14:35', $result);
    }
}
