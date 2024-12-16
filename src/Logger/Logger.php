<?php
declare(strict_types=1);

namespace Mollie\Logger;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\Service\EntityManager\EntityManagerInterface;
use Mollie\Service\EntityManager\ObjectModelUnitOfWork;
use Mollie\Utility\NumberIdempotencyProvider;
use Psr\Log\LoggerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

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

    /**
     * @param string|\Stringable $message
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(self::SEVERITY_ERROR, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function alert($message, array $context = []): void
    {
        $this->log(self::SEVERITY_WARNING, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function critical($message, array $context = []): void
    {
        $this->log(self::SEVERITY_ERROR, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function error($message, array $context = []): void
    {
        $this->log(self::SEVERITY_ERROR, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function warning($message, array $context = []): void
    {
        $this->log(self::SEVERITY_WARNING, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function notice($message, array $context = []): void
    {
        $this->log(self::SEVERITY_INFO, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function info($message, array $context = []): void
    {
        $this->log(self::SEVERITY_INFO, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    public function debug($message, array $context = []): void
    {
        if ((int) $this->configuration->get(Config::MOLLIE_DEBUG_LOG) === Config::DEBUG_LOG_ALL) {
            $this->log(self::SEVERITY_INFO, $message, $context);
        }
    }

    /**
     * @param string|\Stringable $message
     */
    public function log($level, $message, array $context = []): void
    {
        $this->validateMessage($message);

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

    private function logContext($logId, array $context): void
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

    private function getFilledContextWithShopData(array $context): array
    {
        $context['context_id_customer'] = $this->context->getCustomerId();
        $context['id_shop'] = $this->context->getShopId();
        $context['currency'] = $this->context->getCurrencyIso();
        $context['id_language'] = $this->context->getLanguageId();

        return $context;
    }

    /**
     * Validate that the message is a string or Stringable.
     *
     * @param mixed $message
     * @throws \InvalidArgumentException
     */
    private function validateMessage($message): void
    {
        if (!is_string($message) && !$message instanceof \Stringable) {
            throw new \InvalidArgumentException('Message must be a string or Stringable.');
        }
    }
}
