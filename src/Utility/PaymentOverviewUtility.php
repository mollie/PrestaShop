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

namespace Mollie\Utility;

use Mollie\Api\Types\PaymentStatus;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Pure helpers behind the payment overview list. Kept free of Db, Context and the container so
 * the selection rule can be unit tested without booting PrestaShop.
 */
class PaymentOverviewUtility
{
    /**
     * Statuses that are terminal failures and therefore always belong on the overview.
     */
    const TERMINAL_STATUSES = [
        PaymentStatus::STATUS_FAILED,
        PaymentStatus::STATUS_CANCELED,
        PaymentStatus::STATUS_EXPIRED,
    ];

    /**
     * Statuses that only belong on the overview once they have gone stale.
     */
    const STUCK_STATUSES = [
        PaymentStatus::STATUS_OPEN,
        PaymentStatus::STATUS_PENDING,
    ];

    /**
     * Failure codes Mollie documents for cards and card wallets. Closed enum, so the back office
     * can translate every one of them. See the Cards section of the method-specific parameters
     * reference.
     */
    const CARD_FAILURE_REASONS = [
        'authentication_abandoned',
        'authentication_failed',
        'authentication_required',
        'authentication_unavailable_acs',
        'card_declined',
        'card_expired',
        'inactive_card',
        'insufficient_funds',
        'invalid_cvv',
        'invalid_card_holder_name',
        'invalid_card_number',
        'invalid_card_type',
        'possible_fraud',
        'refused_by_issuer',
        'unknown_reason',
    ];

    /**
     * Builds the WHERE fragment selecting the attempts worth showing.
     *
     * An attempt qualifies when it failed outright, or when it is still open or pending, has no
     * order behind it, and has been that way for longer than the grace period.
     *
     * The order_id = 0 condition on the stale branch is load bearing. Bank transfer legitimately
     * sits at open for up to MOLLIE_BANKTRANSFER_DUE_DAYS, but its flow creates the PrestaShop
     * order before inserting the row, so it always carries a real order id and never matches.
     * The same is true of the second chance payment link.
     *
     * @param string $alias table alias the fragment is written against
     * @param int $graceHours
     *
     * @return string
     */
    public static function buildOverviewWhereClause($alias, $graceHours)
    {
        // Whitelist rather than bqSQL(). bqSQL escapes a backtick as \`, but a backslash is not
        // an escape character inside a MySQL quoted identifier, so the escaped backtick still
        // closes the identifier and everything after it becomes SQL.
        $alias = preg_replace('/[^A-Za-z0-9_]/', '', (string) $alias);
        $alias = '' === $alias ? 'a' : $alias;
        $graceHours = max(0, (int) $graceHours);

        return sprintf(
            '(`%1$s`.`bank_status` IN (%2$s) OR (`%1$s`.`bank_status` IN (%3$s) AND `%1$s`.`order_id` = 0 AND `%1$s`.`created_at` < DATE_SUB(NOW(), INTERVAL %4$d HOUR)))',
            $alias,
            self::quoteList(self::TERMINAL_STATUSES),
            self::quoteList(self::STUCK_STATUSES),
            $graceHours
        );
    }

    /**
     * Mirrors buildOverviewWhereClause() in PHP so the selection rule can be asserted directly.
     *
     * @param string $status
     * @param int $orderId
     * @param int $ageHours how long the attempt has existed
     * @param int $graceHours
     *
     * @return bool
     */
    public static function isOverviewAttempt($status, $orderId, $ageHours, $graceHours)
    {
        if (in_array($status, self::TERMINAL_STATUSES, true)) {
            return true;
        }

        if (!in_array($status, self::STUCK_STATUSES, true)) {
            return false;
        }

        if ((int) $orderId > 0) {
            return false;
        }

        return (int) $ageHours > (int) $graceHours;
    }

    /**
     * @param array $statuses
     *
     * @return string
     */
    private static function quoteList(array $statuses)
    {
        $quoted = array_map(static function ($status) {
            return "'" . pSQL($status) . "'";
        }, $statuses);

        return implode(', ', $quoted);
    }
}
