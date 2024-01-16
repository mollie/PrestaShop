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

namespace Mollie\Service;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Exception\MollieApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface ApiServiceInterface
{
    /**
     * @throws ApiException
     * @throws MollieApiException
     */
    public function requestApplePayPaymentSession(?MollieApiClient $api, string $validationUrl): string;
}
