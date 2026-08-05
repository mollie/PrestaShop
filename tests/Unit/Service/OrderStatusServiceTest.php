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

namespace Mollie\Tests\Unit\Service;

use Mollie\Service\OrderStatusService;
use PHPUnit\Framework\TestCase;

class OrderStatusServiceTest extends TestCase
{
    /**
     * @dataProvider backOrderProvider
     *
     * @param bool $stockState
     * @param int $quantityInStock
     * @param int $quantityOrdered
     * @param bool $expected
     */
    public function testIsBackOrder($stockState, $quantityInStock, $quantityOrdered, $expected)
    {
        $this->assertSame(
            $expected,
            OrderStatusService::isBackOrder($stockState, $quantityInStock, $quantityOrdered)
        );
    }

    public function backOrderProvider()
    {
        return [
            // The bug: initial stock exactly 0 with a positive ordered quantity must count as backorder.
            'zero stock, one ordered -> backorder' => [false, 0, 1, true],
            'zero stock, three ordered -> backorder' => [false, 0, 3, true],
            // The only case the old "< 0" check caught.
            'negative stock -> backorder' => [false, -1, 1, true],
            // Regression guard: an in-stock order must never be flagged as backorder.
            'enough stock -> not backorder' => [false, 5, 1, false],
            'exact stock -> not backorder' => [false, 1, 1, false],
            // Nothing ordered at zero stock is not a backorder.
            'zero stock, none ordered -> not backorder' => [false, 0, 0, false],
            // The out-of-stock flag alone forces backorder regardless of quantities.
            'stock state flag -> backorder' => [true, 5, 1, true],
        ];
    }
}
