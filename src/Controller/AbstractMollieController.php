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
use Mollie\Logger\PrestaLoggerInterface;

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
        /** @var PrestaLoggerInterface $logger */
        $logger = $this->module->getService(PrestaLoggerInterface::class);

        if ($value instanceof JsonResponse) {
            if ($value->getStatusCode() === JsonResponse::HTTP_INTERNAL_SERVER_ERROR) {
                $logger->error('Failed to return valid response', [
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
            $logger->error('Could not return ajax response', [
                'response' => json_encode($value ?: []),
                'Exception message' => $exception->getMessage(),
                'Exception code' => $exception->getCode(),
            ]);
        }

        exit;
    }

    protected function applyLock(string $resource): Response
    {
        /** @var PrestaLoggerInterface $logger */
        $logger = $this->module->getService(PrestaLoggerInterface::class);

        try {
            $this->lock->create($resource);

            if (!$this->lock->acquire()) {
                $logger->error('Lock resource conflict', [
                    'resource' => $resource,
                ]);

                return Response::respond(
                    $this->module->l('Resource conflict', self::FILE_NAME),
                    Response::HTTP_CONFLICT
                );
            }
        } catch (\Throwable $exception) {
            $logger->error('Failed to lock process', [
                'Exception message' => $exception->getMessage(),
                'Exception code' => $exception->getCode(),
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
        /** @var PrestaLoggerInterface $logger */
        $logger = $this->module->getService(PrestaLoggerInterface::class);

        try {
            $this->lock->release();
        } catch (\Throwable $exception) {
            $logger->error('Failed to release process', [
                'Exception message' => $exception->getMessage(),
                'Exception code' => $exception->getCode(),
            ]);
        }
    }
}
