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
use Mollie\Subscription\DTO\CreateFreeOrderData;
use Mollie\Subscription\Factory\MollieApiFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentApi
{
    /** @var MollieApiClient */
    private $apiClient;

    public function __construct(MollieApiFactory $mollieApiFactory)
    {
        $this->apiClient = $mollieApiFactory->getMollieClient();
    }

    public function createFreePayment(CreateFreeOrderData $createFreeOrderData)
    {
        return $this->apiClient->payments->create($createFreeOrderData->jsonSerialize());
    }

    public function getPayment(string $transactionId)
    {
        return $this->apiClient->payments->get($transactionId);
    }
}
