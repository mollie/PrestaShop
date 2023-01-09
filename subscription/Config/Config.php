<?php

declare(strict_types=1);

namespace Mollie\Subscription\Config;

use Mollie\Api\Types\MandateMethod;
use Mollie\Subscription\Constants\IntervalConstant;
use Mollie\Subscription\DTO\Object\Interval;

class Config
{
    public const MOLLIE_MODULE_NAME = 'mollie';

    public const SUBSCRIPTION_ATTRIBUTE_GROUP = 'SUBSCRIPTION_ATTRIBUTE_GROUP';
    public const SUBSCRIPTION_ATTRIBUTE_NONE = 'SUBSCRIPTION_ATTRIBUTE_NONE';
    public const SUBSCRIPTION_ATTRIBUTE_DAILY = 'SUBSCRIPTION_ATTRIBUTE_DAILY';
    public const SUBSCRIPTION_ATTRIBUTE_WEEKLY = 'SUBSCRIPTION_ATTRIBUTE_WEEKLY';
    public const SUBSCRIPTION_ATTRIBUTE_MONTHLY = 'SUBSCRIPTION_ATTRIBUTE_MONTHLY';

    public const DESCRIPTION_PREFIX = 'mol';

    public const DB_PREFIX = 'mol_sub_';

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
        ];
    }

    /**
     * @return array<string, Interval>
     */
    public static function getSubscriptionIntervals(): array
    {
        $intervalAmount = 1;

        return [
            Config::SUBSCRIPTION_ATTRIBUTE_DAILY => new Interval($intervalAmount, IntervalConstant::DAY),
            Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY => new Interval($intervalAmount, IntervalConstant::WEEK),
            Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY => new Interval($intervalAmount, IntervalConstant::MONTH),
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
