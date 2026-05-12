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

namespace Mollie\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

// TEMPORARY — remove once Segment integration is verified in production
class SegmentLogger
{
    public static function log(string $eventName, array $properties, bool $success): void
    {
        try {
            $logDir = defined('_PS_MODULE_DIR_') ? _PS_MODULE_DIR_ . 'mollie/logs' : __DIR__ . '/../../../logs';

            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $line = sprintf(
                "[%s] %s | %s\n%s\n%s\n",
                date('Y-m-d H:i:s'),
                $eventName,
                $success ? 'SENT' : 'FAILED',
                json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                str_repeat('-', 80)
            );

            file_put_contents($logDir . '/segment_events.log', $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Never break business logic
        }
    }
}
