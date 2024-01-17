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

use Mollie\Api\MollieApiClient;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface ProfileIdProviderInterface
{
    public function getProfileId(MollieApiClient $apiClient): string;
}
