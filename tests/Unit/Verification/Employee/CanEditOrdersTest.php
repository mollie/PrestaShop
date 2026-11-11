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

namespace Mollie\Tests\Unit\Verification\Employee;

use Employee;
use Mollie\Verification\Employee\CanEditOrders;
use PHPUnit\Framework\TestCase;

class CanEditOrdersTest extends TestCase
{
    public function testAllowsEmployeeWithOrderEditRight(): void
    {
        $this->assertTrue((new CanEditOrders())->verify($this->employee(true)));
    }

    public function testDeniesEmployeeWithoutOrderEditRight(): void
    {
        $this->assertFalse((new CanEditOrders())->verify($this->employee(false)));
    }

    public function testDeniesMissingEmployee(): void
    {
        $this->assertFalse((new CanEditOrders())->verify(null));
    }

    private function employee(bool $canEditOrders): Employee
    {
        $employee = $this->createMock(Employee::class);
        $employee->id = 2;
        $employee->expects($this->once())
            ->method('can')
            ->with('edit', 'AdminOrders')
            ->willReturn($canEditOrders);

        return $employee;
    }
}
