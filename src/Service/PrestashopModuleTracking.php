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

namespace Mollie\Service;

use Module;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Official PrestaShop Segment tracking helper
 * Used for module lifecycle tracking (install, configure, enable, disable, upgrade, uninstall)
 *
 * This is a reusable service provided by PrestaShop for GDPR-compliant analytics.
 */
class PrestashopModuleTracking
{
    /**
     * Send tracking event to Segment.
     *
     * @param string $apiKey - Segment API key (can be overridden by SEGMENT_API_KEY env variable)
     * @param Module $module - Module instance
     * @param string $eventName - Event name (e.g., "Module Installed")
     * @param array $properties - Optional custom properties
     *
     * @return bool - Returns true if event was successfully sent, false otherwise
     */
    public static function track($apiKey, Module $module, $eventName, array $properties = [])
    {
        try {
            if (!empty($_ENV['SEGMENT_API_KEY'])) {
                $apiKey = $_ENV['SEGMENT_API_KEY'];
            }

            if (empty($apiKey)) {
                return false;
            }

            $baseProperties = [
                'shop_url' => defined('_PS_BASE_URL_SSL_') ? _PS_BASE_URL_SSL_ : (defined('_PS_BASE_URL_') ? _PS_BASE_URL_ : ''),
                'ps_version' => defined('_PS_VERSION_') ? _PS_VERSION_ : '',
                'php_version' => PHP_VERSION,
                'module_version' => property_exists($module, 'version') ? $module->version : '',
            ];

            if (!empty($properties)) {
                $baseProperties['custom'] = $properties;
            }

            if (method_exists($module, 'getService')) {
                $serviceName = sprintf('%s.ps_accounts_facade', $module->name);
                $accountsFacade = $module->getService($serviceName);

                if ($accountsFacade && method_exists($accountsFacade, 'getPsAccountsService')) {
                    $psAccountsService = $accountsFacade->getPsAccountsService();
                    $baseProperties = array_merge($baseProperties, [
                        'user_id' => $psAccountsService->getUserUuid(),
                        'email' => $psAccountsService->getEmail(),
                        'shop_id' => $psAccountsService->getShopUuid(),
                    ]);
                }
            }

            $payload = [
                'anonymousId' => $module->name,
                'event' => $eventName,
                'properties' => $baseProperties,
            ];

            if (class_exists('\\Segment')) {
                \Segment::init($apiKey);
                \Segment::track($payload);
                \Segment::flush();
            } elseif (class_exists('\\Segment\\Segment')) {
                \Segment\Segment::init($apiKey);
                \Segment\Segment::track($payload);
                \Segment\Segment::flush();
            } else {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
