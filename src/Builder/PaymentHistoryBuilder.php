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

namespace Mollie\Builder;

use Mollie\Adapter\ToolsAdapter;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentHistoryBuilder
{
    const TYPE_RANKS = [
        'created' => 0,
        'authorized' => 1,
        'paid' => 2,
        'captured' => 3,
        'refunded' => 4,
        'chargeback' => 5,
        'chargeback_reversed' => 6,
        'canceled' => 7,
        'expired' => 8,
        'failed' => 9,
    ];

    const NEGATIVE_TYPES = ['refunded', 'chargeback', 'chargeback_reversed'];

    /**
     * @var ToolsAdapter
     */
    private $tools;

    public function __construct(ToolsAdapter $tools)
    {
        $this->tools = $tools;
    }

    /**
     * @param \Mollie\Api\Resources\Payment|\stdClass $payment
     * @param string|null $authorizedAtFallback PrestaShop order history date, used when Mollie no longer returns authorizedAt after capture
     *
     * @return array list of events, newest first
     */
    public function build($payment, $authorizedAtFallback = null): array
    {
        $events = [];

        $this->addPaymentEvents($events, $payment, $authorizedAtFallback);
        $this->addCaptureEvents($events, $this->getEmbedded($payment, 'captures'));
        $this->addRefundEvents($events, $this->getEmbedded($payment, 'refunds'));
        $this->addChargebackEvents($events, $this->getEmbedded($payment, 'chargebacks'));

        usort($events, function (array $left, array $right) {
            $dateComparison = strcmp($right['sort_date'], $left['sort_date']);

            if ($dateComparison !== 0) {
                return $dateComparison;
            }

            return self::TYPE_RANKS[$right['type']] - self::TYPE_RANKS[$left['type']];
        });

        return $events;
    }

    private function addPaymentEvents(array &$events, $payment, $authorizedAtFallback)
    {
        $amount = $this->getField($payment, 'amount');

        $createdDetails = [];
        $this->addDetail($createdDetails, 'method', $this->getField($payment, 'method'));
        $this->addDetail($createdDetails, 'expires_at', $this->formatDate($this->getField($payment, 'expiresAt')));
        $this->addDetail($createdDetails, 'due_date', $this->formatPlainDate($this->getField($payment, 'dueDate')));
        $this->addEvent($events, 'created', $this->getField($payment, 'createdAt'), $amount, null, null, $createdDetails);

        $authorizedDetails = [];
        $this->addDetail($authorizedDetails, 'capture_before', $this->formatDate($this->getField($payment, 'captureBefore'), false));
        $this->addDetail($authorizedDetails, 'capture_mode', $this->getField($payment, 'captureMode'));
        $authorizedAt = $this->getField($payment, 'authorizedAt');
        if ((!is_string($authorizedAt) || $authorizedAt === '') && is_string($authorizedAtFallback) && $authorizedAtFallback !== '') {
            $authorizedAt = $authorizedAtFallback;
        }
        $this->addEvent($events, 'authorized', $authorizedAt, $amount, null, null, $authorizedDetails);

        $paidDetails = [];
        $this->addDetail($paidDetails, 'settlement_amount', $this->formatAmount($this->getField($payment, 'settlementAmount')));
        $this->addEvent($events, 'paid', $this->getField($payment, 'paidAt'), $amount, null, null, $paidDetails);

        $this->addEvent($events, 'canceled', $this->getField($payment, 'canceledAt'), $amount);
        $this->addEvent($events, 'expired', $this->getField($payment, 'expiredAt'), $amount);
        $this->addEvent($events, 'failed', $this->getField($payment, 'failedAt'), $amount);
    }

    private function addCaptureEvents(array &$events, array $captures)
    {
        foreach ($captures as $capture) {
            $details = [];
            $this->addDetail($details, 'settlement_amount', $this->formatAmount($this->getField($capture, 'settlementAmount')));

            $this->addEvent(
                $events,
                'captured',
                $this->getField($capture, 'createdAt'),
                $this->getField($capture, 'amount'),
                $this->getField($capture, 'status'),
                $this->getField($capture, 'id'),
                $details
            );
        }
    }

    private function addRefundEvents(array &$events, array $refunds)
    {
        foreach ($refunds as $refund) {
            $details = [];
            $this->addDetail($details, 'settlement_amount', $this->formatAmount($this->getField($refund, 'settlementAmount')));

            $this->addEvent(
                $events,
                'refunded',
                $this->getField($refund, 'createdAt'),
                $this->getField($refund, 'amount'),
                $this->getField($refund, 'status'),
                $this->getField($refund, 'id'),
                $details
            );
        }
    }

    private function addChargebackEvents(array &$events, array $chargebacks)
    {
        foreach ($chargebacks as $chargeback) {
            $amount = $this->getField($chargeback, 'amount');
            $reference = $this->getField($chargeback, 'id');

            $details = [];
            $this->addDetail($details, 'reason', $this->formatReason($this->getField($chargeback, 'reason')));

            $this->addEvent($events, 'chargeback', $this->getField($chargeback, 'createdAt'), $amount, null, $reference, $details);
            $this->addEvent($events, 'chargeback_reversed', $this->getField($chargeback, 'reversedAt'), $amount, null, $reference);
        }
    }

    private function addEvent(array &$events, string $type, $isoDate, $amount, $status = null, $reference = null, array $details = [])
    {
        if (!is_string($isoDate) || $isoDate === '') {
            return;
        }

        $timestamp = strtotime($isoDate);

        if ($timestamp === false) {
            return;
        }

        $localDate = date('Y-m-d H:i:s', $timestamp);
        $value = $this->getAmountField($amount, 'value');
        $currency = $this->getAmountField($amount, 'currency');
        $negative = in_array($type, self::NEGATIVE_TYPES, true);

        $events[] = [
            'type' => $type,
            'date' => (string) $this->tools->displayDate($localDate, true),
            'sort_date' => $localDate,
            'amount' => $value,
            'currency' => $currency,
            'amount_formatted' => $this->formatPrice($value, $currency, $negative),
            'negative' => $negative,
            'status' => $this->toNullableString($status),
            'reference' => $this->toNullableString($reference),
            'details' => $details,
        ];
    }

    private function addDetail(array &$details, string $label, $value)
    {
        if ($value === null || $value === '') {
            return;
        }

        $details[] = [
            'label' => $label,
            'value' => (string) $value,
        ];
    }

    private function getEmbedded($payment, string $collection): array
    {
        $embedded = $this->getField($payment, '_embedded');
        $items = $this->getField($embedded, $collection);

        if (!is_array($items)) {
            return [];
        }

        return array_filter($items, 'is_object');
    }

    private function getField($source, string $field)
    {
        if (!is_object($source) || !isset($source->{$field})) {
            return null;
        }

        return $source->{$field};
    }

    private function getAmountField($amount, string $field)
    {
        $value = $this->getField($amount, $field);

        if ($value === null || is_object($value) || is_array($value)) {
            return null;
        }

        return (string) $value;
    }

    private function formatAmount($amount)
    {
        $value = $this->getAmountField($amount, 'value');

        if ($value === null) {
            return null;
        }

        return $this->formatPrice((string) abs((float) $value), $this->getAmountField($amount, 'currency'), (float) $value < 0);
    }

    private function formatPrice($value, $currency, bool $negative)
    {
        if ($value === null) {
            return null;
        }

        $currencyObject = null;
        if ($currency !== null) {
            $currencyObject = (object) ['iso_code' => $currency];
        }

        $formatted = (string) $this->tools->displayPrice((float) $value, $currencyObject);

        if (!$negative) {
            return $formatted;
        }

        return '-' . $formatted;
    }

    private function formatDate($isoDate, bool $withTime = true)
    {
        if (!is_string($isoDate) || $isoDate === '') {
            return null;
        }

        $timestamp = strtotime($isoDate);

        if ($timestamp === false) {
            return null;
        }

        return (string) $this->tools->displayDate(date('Y-m-d H:i:s', $timestamp), $withTime);
    }

    private function formatPlainDate($date)
    {
        if (!is_string($date) || $date === '') {
            return null;
        }

        return (string) $this->tools->displayDate($date, false);
    }

    private function formatReason($reason)
    {
        if (is_object($reason)) {
            return $this->toNullableString($this->getField($reason, 'description'));
        }

        return $this->toNullableString($reason);
    }

    private function toNullableString($value)
    {
        if ($value === null || is_object($value) || is_array($value)) {
            return null;
        }

        return (string) $value;
    }
}
