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
use Mollie\Api\Resources\BaseResource;
use Mollie\Api\Resources\Mandate as MandateMollie;
use Mollie\Subscription\DTO\CreateMandateData;
use Mollie\Subscription\Factory\MollieApiFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MandateApi
{
    /** @var MollieApiClient */
    private $apiClient;

    public function __construct(MollieApiFactory $mollieApiFactory)
    {
        $this->apiClient = $mollieApiFactory->getMollieClient();
    }

    /**
     * @return BaseResource|MandateMollie
     */
    public function createMandate(CreateMandateData $mandateData)
    {
        return $this->apiClient->mandates->createForId($mandateData->getCustomerId(), $mandateData->jsonSerialize());
    }
}
