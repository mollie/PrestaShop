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

namespace Mollie\Provider;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProfileIdProvider implements ProfileIdProviderInterface
{
    public function getProfileId(MollieApiClient $apiClient): string
    {
        try {
            return $apiClient->profiles->get('me')->id;
        } catch (ApiException $e) {
            return '';
        }
    }
}
