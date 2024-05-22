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

namespace Mollie\Subscription\Provider;

use Combination;
use DateInterval;
use DateTime;
use DateTimeZone;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\DTO\Object\Interval;
use Mollie\Subscription\Exception\SubscriptionIntervalException;
use Mollie\Subscription\Utility\Clock;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionStartDateProvider
{
    /** @var ConfigurationAdapter */
    private $configuration;

    public function __construct(ConfigurationAdapter $configuration, Clock $clock)
    {
        $this->configuration = $configuration;
        $this->clock = $clock;
    }

    /**
     * Returns subscription date time
     *
     * @throws SubscriptionIntervalException
     */
    public function getSubscriptionStartDate(Combination $combination): string
    {
        $currentTime = new DateTime('now', new DateTimeZone('UTC'));

        foreach ($combination->getWsProductOptionValues() as $attribute) {
            switch ($attribute['id']) {
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_DAILY):
                    $interval = new DateInterval('P1D');
                    break;
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_WEEKLY):
                    $interval = new DateInterval('P7D');
                    break;
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_MONTHLY):
                    $interval = new DateInterval('P1M');
                    break;
                case $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_YEARLY):
                    $interval = new DateInterval('P1Y');
                    break;
                default:
                    throw new SubscriptionIntervalException(sprintf('No interval exists for this %s attribute', $combination->id));
            }

            // Add the interval to the current time
            $currentTime->add($interval);
            return $currentTime->format('Y-m-d');
        }

        throw new SubscriptionIntervalException(sprintf('No interval exists for this %s attribute', $combination->id));
    }
}
