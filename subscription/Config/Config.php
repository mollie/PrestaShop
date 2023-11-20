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

declare(strict_types=1);

namespace Mollie\Subscription\Config;

use Mollie\Api\Types\MandateMethod;
use Mollie\Subscription\Constants\IntervalConstant;
use Mollie\Subscription\DTO\Object\Interval;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Config
{
    public const MOLLIE_MODULE_NAME = 'mollie';

    public const SUBSCRIPTION_ATTRIBUTE_GROUP = 'SUBSCRIPTION_ATTRIBUTE_GROUP';
    public const SUBSCRIPTION_ATTRIBUTE_NONE = 'SUBSCRIPTION_ATTRIBUTE_NONE';
    public const SUBSCRIPTION_ATTRIBUTE_DAILY = 'SUBSCRIPTION_ATTRIBUTE_DAILY';
    public const SUBSCRIPTION_ATTRIBUTE_WEEKLY = 'SUBSCRIPTION_ATTRIBUTE_WEEKLY';
    public const SUBSCRIPTION_ATTRIBUTE_MONTHLY = 'SUBSCRIPTION_ATTRIBUTE_MONTHLY';
    public const SUBSCRIPTION_ATTRIBUTE_YEARLY = 'SUBSCRIPTION_ATTRIBUTE_YEARLY';

    public const DESCRIPTION_PREFIX = 'mol';

    public const DB_PREFIX = 'mol_';

    /**
     * @return array<string, string>
     */
    public static function getSubscriptionAttributeOptions(): array
    {
        return [
            'None' => Config::SUBSCRIPTION_ATTRIBUTE_NONE,
            'Daily' => Config::SUBSCRIPTION_ATTRIBUTE_DAILY,
            'Weekly' => Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY,
            'Monthly' => Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY,
            'Yearly' => Config::SUBSCRIPTION_ATTRIBUTE_YEARLY,
        ];
    }

    /**
     * @return array<string, Interval>
     */
    public static function getSubscriptionIntervals(): array
    {
        $intervalAmount = 1;
        $intervalAmountForYears = 12;

        return [
            Config::SUBSCRIPTION_ATTRIBUTE_DAILY => new Interval($intervalAmount, IntervalConstant::DAY),
            Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY => new Interval($intervalAmount, IntervalConstant::WEEK),
            Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY => new Interval($intervalAmount, IntervalConstant::MONTH),
            Config::SUBSCRIPTION_ATTRIBUTE_YEARLY => new Interval($intervalAmountForYears, IntervalConstant::MONTHS),
        ];
    }

    /**
     * @return string[]
     */
    public static function getAvailableMandateMethods(): array
    {
        return [
            MandateMethod::DIRECTDEBIT,
            MandateMethod::CREDITCARD,
            MandateMethod::PAYPAL,
        ];
    }
}
