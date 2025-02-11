<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\Controller;

use Mollie\Errors\Error;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Infrastructure\Adapter\Lock;
use Mollie\Infrastructure\Response\JsonResponse;
use Mollie\Infrastructure\Response\Response;
use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AbstractMollieController extends \ModuleFrontControllerCore
{
    private const FILE_NAME = 'AbstractMollieController';

    /** @var Lock */
    private $lock;

    /** @var \Mollie */
    public $module;

    public function __construct()
    {
        parent::__construct();

        $this->lock = $this->module->getService(Lock::class);
    }

    protected function respond($status, $statusCode = HttpStatusCode::HTTP_OK, $message = ''): void
    {
        http_response_code($statusCode);

        $response = ['status' => $status];

        if ($message) {
            $response['error'] = new Error($statusCode, $message);
        }

        $this->ajaxRender(json_encode($response));
    }

    protected function ajaxRender($value = null, $controller = null, $method = null): void
    {
        // TODO remove this later
        parent::ajaxRender($value, $controller, $method);

        exit;
    }

    protected function ajaxResponse($value, $controller = null, $method = null): void
    {
        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        if ($value instanceof JsonResponse) {
            if ($value->getStatusCode() === JsonResponse::HTTP_INTERNAL_SERVER_ERROR) {
                $logger->error(sprintf('%s - Failed to return valid response', self::FILE_NAME), [
                    'context' => [
                        'response' => $value->getContent(),
                    ],
                ]);
            }

            http_response_code($value->getStatusCode());

            $value = $value->getContent();
        }

        try {
            $this->ajaxRender($value, $controller, $method);
        } catch (\Throwable $exception) {
            $logger->error(sprintf('%s - Could not return ajax response', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);
        }

        exit;
    }

    protected function applyLock(string $resource): Response
    {
        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            $this->lock->create($resource);

            if (!$this->lock->acquire()) {
                $logger->error(sprintf('%s - Lock resource conflict', self::FILE_NAME));

                return Response::respond(
                    $this->module->l('Resource conflict', self::FILE_NAME),
                    Response::HTTP_CONFLICT
                );
            }
        } catch (\Throwable $exception) {
            $logger->error(sprintf('%s - Failed to lock process', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            return Response::respond(
                $this->module->l('Internal error', self::FILE_NAME),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return Response::respond(
            '',
            Response::HTTP_OK
        );
    }

    protected function releaseLock(): void
    {
        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            $this->lock->release();
        } catch (\Throwable $exception) {
            $logger->error(sprintf('%s - Failed to release process', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);
        }
    }
}
