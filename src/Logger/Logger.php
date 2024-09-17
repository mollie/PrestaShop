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

namespace Mollie\Logger;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\Service\EntityManager\EntityManagerInterface;
use Mollie\Service\EntityManager\ObjectModelUnitOfWork;
use Mollie\Utility\NumberIdempotencyProvider;

class Logger implements LoggerInterface
{
    public const FILE_NAME = 'Logger';

    public const LOG_OBJECT_TYPE = 'mollieLog';

    public const SEVERITY_INFO = 1;
    public const SEVERITY_WARNING = 2;
    public const SEVERITY_ERROR = 3;

    private $logFormatter;
    private $configuration;
    private $context;
    private $entityManager;
    private $idempotencyProvider;
    private $prestashopLoggerRepository;

    public function __construct(
        LogFormatterInterface $logFormatter,
        ConfigurationAdapter $configuration,
        Context $context,
        EntityManagerInterface $entityManager,
        NumberIdempotencyProvider $idempotencyProvider,
        PrestashopLoggerRepositoryInterface $prestashopLoggerRepository
    ) {
        $this->logFormatter = $logFormatter;
        $this->configuration = $configuration;
        $this->context = $context;
        $this->entityManager = $entityManager;
        $this->idempotencyProvider = $idempotencyProvider;
        $this->prestashopLoggerRepository = $prestashopLoggerRepository;
    }

    public function emergency($message, array $context = [])
    {
        $this->log(
            $this->configuration->getAsInteger(
                'PS_LOGS_BY_EMAIL',
                $this->context->getShopId()
            ),
            $message,
            $context
        );
    }

    public function alert($message, array $context = [])
    {
        $this->log(self::SEVERITY_WARNING, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log(
            $this->configuration->getAsInteger(
                'PS_LOGS_BY_EMAIL',
                $this->context->getShopId()
            ),
            $message,
            $context
        );
    }

    public function error($message, array $context = [])
    {
        $this->log(self::SEVERITY_ERROR, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log(self::SEVERITY_WARNING, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log(self::SEVERITY_INFO, $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log(self::SEVERITY_INFO, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        if ((int) $this->configuration->get(Config::MOLLIE_DEBUG_LOG) === Config::DEBUG_LOG_ALL) {
            $this->log(self::SEVERITY_INFO, $message, $context);
        }
    }

    public function log($level, $message, array $context = [])
    {
        $idempotencyKey = $this->idempotencyProvider->getIdempotencyKey();

        \PrestaShopLogger::addLog(
            $this->logFormatter->getMessage($message),
            $level,
            null,
            self::LOG_OBJECT_TYPE,
            $idempotencyKey
        );

        $logId = $this->prestashopLoggerRepository->getLogIdByObjectId(
            $idempotencyKey,
            $this->context->getShopId()
        );

        if (!$logId) {
            return;
        }

        $this->logContext($logId, $context);
    }

    private function logContext($logId, array $context)
    {
        $request = '';
        $response = '';

        if (isset($context['request'])) {
            $request = $context['request'];
            unset($context['request']);
        }

        if (isset($context['response'])) {
            $response = $context['response'];
            unset($context['response']);
        }

        $log = new \MolLog();
        $log->id_log = $logId;
        $log->id_shop = $this->context->getShopId();
        $log->context = json_encode($this->getFilledContextWithShopData($context));
        $log->request = json_encode($request);
        $log->response = json_encode($response);

        $this->entityManager->persist($log, ObjectModelUnitOfWork::UNIT_OF_WORK_SAVE);
        $this->entityManager->flush();
    }

    private function getFilledContextWithShopData(array $context = [])
    {
        $context['context_id_customer'] = $this->context->getCustomerId();
        $context['id_shop'] = $this->context->getShopId();
        $context['currency'] = $this->context->getCurrencyIso();
        $context['id_language'] = $this->context->getLanguageId();

        return $context;
    }
}
