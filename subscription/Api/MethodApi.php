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

namespace Mollie\Subscription\Api;

use Mollie\Api\MollieApiClient;
use Mollie\Subscription\Factory\MollieApiFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MethodApi
{
    /** @var MollieApiClient */
    private $apiClient;

    public function __construct(MollieApiFactory $mollieApiFactory)
    {
        $this->apiClient = $mollieApiFactory->getMollieClient();
    }

    public function getMethodsForFirstPayment(string $locale, string $currencyIso)
    {
        return $this->apiClient->methods->allActive(
            [
                'locale' => $locale,
                'sequenceType' => \Mollie\Api\Types\SequenceType::SEQUENCETYPE_FIRST,
                'amount' => [
                    'value' => '0.00',
                    'currency' => $currencyIso,
                ],
            ]
        );
    }
}
