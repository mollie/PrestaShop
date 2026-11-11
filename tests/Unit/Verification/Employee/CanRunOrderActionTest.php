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
use Mollie\Verification\Employee\CanRunOrderAction;
use PHPUnit\Framework\TestCase;

class CanRunOrderActionTest extends TestCase
{
    public function provideOrderActions(): array
    {
        return [
            ['refund'],
            ['refundAll'],
            ['capture'],
            ['captureAll'],
            ['ship'],
            ['shipAll'],
            ['cancel'],
            ['cancelAll'],
        ];
    }

    /**
     * @dataProvider provideOrderActions
     */
    public function testOrderActionIsAllowedWithOrderEditRight(string $action): void
    {
        $this->assertTrue((new CanRunOrderAction())->verify($action, $this->employee(true)));
    }

    /**
     * @dataProvider provideOrderActions
     */
    public function testOrderActionIsDeniedWithoutOrderEditRight(string $action): void
    {
        $this->assertFalse((new CanRunOrderAction())->verify($action, $this->employee(false)));
    }

    /**
     * @dataProvider provideOrderActions
     */
    public function testOrderActionIsDeniedWithoutEmployee(string $action): void
    {
        $this->assertFalse((new CanRunOrderAction())->verify($action, null));
    }

    public function testOrderEditRightIsCheckedOnOrdersTab(): void
    {
        $employee = $this->createMock(Employee::class);
        $employee->id = 2;
        $employee->expects($this->once())
            ->method('can')
            ->with('edit', 'AdminOrders')
            ->willReturn(true);

        (new CanRunOrderAction())->verify('refund', $employee);
    }

    public function testOtherActionsAreNotRestricted(): void
    {
        $employee = $this->employee(false);
        $employee->expects($this->never())->method('can');

        $this->assertTrue((new CanRunOrderAction())->verify('retrieve', $employee));
        $this->assertTrue((new CanRunOrderAction())->verify('getShipmentInfo', $employee));
    }

    private function employee(bool $canEditOrders): Employee
    {
        $employee = $this->createMock(Employee::class);
        $employee->id = 2;
        $employee->method('can')->willReturn($canEditOrders);

        return $employee;
    }
}
