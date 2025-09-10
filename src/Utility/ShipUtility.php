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

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShipUtility
{
    /**
     * @param array $lines
     * @param array $allOrderLines
     * @return array
     */
    public static function getShipLines(array $lines, $allOrderLines)
    {
        $shipLines = [];
        $lineIds = array_column($lines, 'id');

        foreach ($allOrderLines as $orderLine) {
            /** @var object $orderLine */
            if (!empty($orderLine->metadata) && in_array($orderLine->metadata->idProduct, $lineIds)) {
                $shipLines[] = [
                    'id' => $orderLine->id,
                    'quantity' => $orderLine->quantity,
                ];
            }
        }

        return $shipLines;
    }

    public static function isOrderLinesShipPossible(array $lines, $availableShipment)
    {
        $shippedAmount = 0;
        foreach ($lines as $line) {
            $lineShipAmount = NumberUtility::times($line['unitPrice']['value'], $line['quantity']);
            $shippedAmount = NumberUtility::plus($shippedAmount, $lineShipAmount);
        }

        return NumberUtility::isLowerOrEqualThan($shippedAmount, $availableShipment['value']);
    }

    public static function getShippedAmount($orderShipments)
    {
        $shipAmount = 0;
        foreach ($orderShipments as $shipment) {
            if ($shipment->status === 'shipped') {
                $shipAmount = NumberUtility::plus((float) $shipAmount, (float) $shipment->amount->value);
            }
        }

        return $shipAmount;
    }

    public static function getShippableAmount($orderAmount, $shippedAmount)
    {
        return NumberUtility::minus((float) $orderAmount, (float) $shippedAmount);
    }
}
