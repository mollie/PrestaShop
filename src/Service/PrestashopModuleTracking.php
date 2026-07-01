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

class PrestashopModuleTracking
{
    public static function track($apiKey, Module $module, $eventName, array $properties = [])
    {
        try {
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

            try {
                if (method_exists($module, 'getService')) {
                    $serviceName = sprintf('%s.PsAccountsFacade', $module->name);
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
            } catch (\Throwable $e) {
                // PS Accounts enrichment is optional — proceed without it
            }

            $payload = [
                'anonymousId' => $module->name,
                'event' => $eventName,
                'properties' => $baseProperties,
            ];

            if (!class_exists('\\Segment') && !class_exists('\\Segment\\Segment')) {
                $psAccountsAutoloader = defined('_PS_MODULE_DIR_')
                    ? _PS_MODULE_DIR_ . 'ps_accounts/vendor/autoload.php'
                    : null;
                if ($psAccountsAutoloader && file_exists($psAccountsAutoloader)) {
                    require_once $psAccountsAutoloader;
                }
            }

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
