<?php

namespace Mollie\Infrastructure\Response;

use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

class JsonResponse extends BaseJsonResponse
{
    /**
     * @param mixed $data
     */
    public function __construct($data = null, int $status = 200, array $headers = [])
    {
        parent::__construct($data, $status, $headers);
    }

    public static function success(array $data, int $status = 200): self
    {
        return new self([
            'success' => true,
            'errors' => [],
            'data' => $data,
        ], $status);
    }

    /**
     * @param string|array $error
     */
    public static function error($error, int $status = 400): self
    {
        if (!is_array($error)) {
            $error = [$error];
        }

        return new self([
            'success' => false,
            'errors' => $error,
            'data' => [],
        ], $status);
    }
}
