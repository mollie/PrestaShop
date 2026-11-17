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
use Mollie\Verification\Employee\CanAccessTab;
use PHPUnit\Framework\TestCase;

class CanAccessTabTest extends TestCase
{
    public function testAllowsWhenEmployeeCanAccessTab(): void
    {
        $employee = $this->createMock(Employee::class);
        $employee->id = 2;
        $employee->expects($this->once())->method('can')->with('edit', 'AdminOrders')->willReturn(true);

        $this->assertTrue((new CanAccessTab())->verify($employee, 'edit', 'AdminOrders'));
    }

    public function testDeniesWhenEmployeeCannotAccessTab(): void
    {
        $employee = $this->createMock(Employee::class);
        $employee->id = 2;
        $employee->expects($this->once())->method('can')->with('edit', 'AdminOrders')->willReturn(false);

        $this->assertFalse((new CanAccessTab())->verify($employee, 'edit', 'AdminOrders'));
    }

    public function testDeniesNullEmployee(): void
    {
        $this->assertFalse((new CanAccessTab())->verify(null, 'edit', 'AdminOrders'));
    }

    public function testDeniesUnloadedEmployee(): void
    {
        $employee = $this->createMock(Employee::class);
        $employee->id = 0;
        $employee->expects($this->never())->method('can');

        $this->assertFalse((new CanAccessTab())->verify($employee, 'edit', 'AdminOrders'));
    }

    public function testPassesViewRightAndTabThrough(): void
    {
        $employee = $this->createMock(Employee::class);
        $employee->id = 2;
        $employee->expects($this->once())->method('can')->with('view', 'AdminMollieLogs')->willReturn(true);

        $this->assertTrue((new CanAccessTab())->verify($employee, 'view', 'AdminMollieLogs'));
    }

    public function testPassesEditRightAndTabThrough(): void
    {
        $employee = $this->createMock(Employee::class);
        $employee->id = 2;
        $employee->expects($this->once())->method('can')->with('edit', 'AdminMolliePaymentMethods')->willReturn(true);

        $this->assertTrue((new CanAccessTab())->verify($employee, 'edit', 'AdminMolliePaymentMethods'));
    }
}
