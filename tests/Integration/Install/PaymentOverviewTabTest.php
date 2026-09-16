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

namespace Mollie\Tests\Integration\Install;

use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Utility\PaymentOverviewUtility;

class PaymentOverviewTabTest extends BaseTestCase
{
    public function testTheSidebarEntryIsInstalledAndActive()
    {
        $tabId = (int) \Tab::getIdFromClassName(\Mollie::ADMIN_MOLLIE_PAYMENT_OVERVIEW_PARENT_CONTROLLER);

        $this->assertGreaterThan(0, $tabId, 'The payment overview sidebar entry was not installed.');

        $tab = new \Tab($tabId);

        $this->assertTrue((bool) $tab->active);
        $this->assertSame('mollie', $tab->module);
    }

    /**
     * Installer::installSpecificTabs() runs after parent::install() has processed getTabs(), so it
     * is the one that decides the final parent. A regression there leaves the page reachable but
     * absent from the Mollie tab strip.
     */
    public function testTheListControllerIsParentedToTheMollieTabStrip()
    {
        $tabId = (int) \Tab::getIdFromClassName(\Mollie::ADMIN_MOLLIE_PAYMENT_OVERVIEW_CONTROLLER);

        $this->assertGreaterThan(0, $tabId, 'The payment overview controller was not installed.');

        $tab = new \Tab($tabId);

        $this->assertTrue((bool) $tab->active);
        $this->assertSame('mollie', $tab->module);
        $this->assertSame(
            (int) \Tab::getIdFromClassName('AdminMollieAuthenticationParent'),
            (int) $tab->id_parent
        );
    }

    /**
     * CREATE TABLE IF NOT EXISTS never touches an existing table, so the indexes have to be
     * registered in the create statement, in the alter guards and in the upgrade file. This is the
     * assertion that catches a site that was missed.
     *
     * @dataProvider provideExpectedIndexes
     */
    public function testTheOverviewIndexesExist(string $index, array $columns)
    {
        $rows = \Db::getInstance()->executeS('
            SELECT `COLUMN_NAME`, `SEQ_IN_INDEX`
            FROM information_schema.statistics
            WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '"
                AND TABLE_NAME = "' . _DB_PREFIX_ . 'mollie_payments"
                AND INDEX_NAME = "' . pSQL($index) . '"
            ORDER BY `SEQ_IN_INDEX`;
        ');

        $this->assertNotEmpty($rows, sprintf('Index %s is missing from mollie_payments.', $index));
        $this->assertSame($columns, array_column($rows, 'COLUMN_NAME'));
    }

    public function provideExpectedIndexes(): array
    {
        return [
            // Serves a status filter, which would otherwise walk the whole created_at index.
            'status then date' => ['mollie_payments_status_created', ['bank_status', 'created_at']],
            // Serves the default view, which sorts on created_at and stops at the page size.
            'date alone' => ['mollie_payments_created_at', ['created_at']],
        ];
    }

    /**
     * Runs the real WHERE clause against seeded rows, so the SQL and the PHP mirror in
     * PaymentOverviewUtility cannot drift apart.
     */
    public function testTheWhereClauseSelectsTheSameRowsAsTheRule()
    {
        $graceHours = 24;
        $attempts = [
            'failed_no_order' => ['failed', 0, 1, true],
            'canceled_no_order' => ['canceled', 0, 1, true],
            'expired_no_order' => ['expired', 0, 1, true],
            'open_bank_transfer_with_order' => ['open', 77, 24 * 14, false],
            'pending_with_order' => ['pending', 77, 24 * 14, false],
            'open_inside_grace' => ['open', 0, 1, false],
            'open_past_grace' => ['open', 0, 48, true],
            'pending_past_grace' => ['pending', 0, 48, true],
            'paid_no_order' => ['paid', 0, 48, false],
            'authorized_no_order' => ['authorized', 0, 48, false],
        ];

        foreach ($attempts as $transactionId => list($status, $orderId, $ageHours)) {
            \Db::getInstance()->insert('mollie_payments', [
                'transaction_id' => pSQL($transactionId),
                'cart_id' => 0,
                'order_id' => (int) $orderId,
                'order_reference' => '',
                'method' => 'ideal',
                'bank_status' => pSQL($status),
                'created_at' => ['type' => 'sql', 'value' => 'DATE_SUB(NOW(), INTERVAL ' . (int) $ageHours . ' HOUR)'],
            ]);
        }

        $selected = \Db::getInstance()->executeS('
            SELECT `transaction_id`
            FROM `' . _DB_PREFIX_ . 'mollie_payments` a
            WHERE a.`transaction_id` IN ("' . implode('", "', array_keys($attempts)) . '")
                AND ' . PaymentOverviewUtility::buildOverviewWhereClause('a', $graceHours) . ';
        ');
        $selected = array_column($selected ?: [], 'transaction_id');

        foreach ($attempts as $transactionId => list($status, $orderId, $ageHours, $expected)) {
            $this->assertSame(
                $expected,
                in_array($transactionId, $selected, true),
                sprintf('"%s" was selected by the query but the rule says otherwise.', $transactionId)
            );
            $this->assertSame(
                $expected,
                PaymentOverviewUtility::isOverviewAttempt($status, $orderId, $ageHours, $graceHours),
                sprintf('"%s" disagrees between the query and the PHP mirror.', $transactionId)
            );
        }
    }
}
