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

namespace Mollie\Tests\Mocks\Service;

use Mollie\Api\MollieApiClient;
use Mollie\Service\ApiServiceInterface;

class ApiServiceMock implements ApiServiceInterface
{
    public function requestApplePayPaymentSession(?MollieApiClient $api, string $validationUrl): string
    {
        return json_encode([
            'epochTimestamp' => 1649750949380,
            'expiresAt' => 1649754549380,
            'merchantSessionIdentifier' => 'SSHD93335E905674653A0A1B3E9B4A21F4A_BB8E62003687F8FCC159B2B83AAFC02DB625F1F1E3997CCC2FE2CFD11F636558',
            'nonce' => 'cc80cf2c',
            'merchantIdentifier' => 'D7B6DD4A8788D5F0E6F7F578925FA4E667351E437C9345C01EF8FA42400571D1',
            'domainName' => 'margud.eu.ngrok.io',
            'displayName' => 'Invertus UAB',
            'signature' => 'test-signature',
            'operationalAnalyticsIdentifier' => 'Invertus UAB:D7B6DD4A8788D5F0E6F7F578925FA4E667351E437C9345C01EF8FA42400571D1',
            'retries' => 0,
            'pspId' => 'D9C7F701C8C6F2C6F3D656C09944E32200B176F152E58D9140C1C53AA8246E60',
        ]);
    }
}
