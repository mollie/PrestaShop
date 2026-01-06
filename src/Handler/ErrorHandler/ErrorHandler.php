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

namespace Mollie\Handler\ErrorHandler;

use Configuration;
use Mollie;
use Mollie\Config\Config;
use Mollie\Factory\ModuleFactory;
use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ErrorHandler
{
    /** @var ErrorHandler */
    private static $instance;

    /** @var Mollie */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    /**
     * @throws \Throwable
     */
    public function handle(\Throwable $error, ?int $code = null, ?bool $throw = true): void
    {
        if ((int) Configuration::get(Config::MOLLIE_DEBUG_LOG) === Config::DEBUG_LOG_ERRORS) {
            /** @var Logger $logger * */
            $logger = $this->module->getService(LoggerInterface::class);

            $logger->error($error->getMessage(), [
                'exceptions' => ExceptionUtility::getExceptions($error),
            ]);
        }

        if ($code && true === $throw) {
            http_response_code($code);
            throw $error;
        }
    }

    public static function getInstance(Mollie $module = null): ErrorHandler
    {
        if (!$module) {
            $module = (new ModuleFactory())->getModule();
        }

        if (self::$instance === null) {
            self::$instance = new ErrorHandler($module);
        }

        return self::$instance;
    }
}
