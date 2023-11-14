<?php

namespace Mollie\Infrastructure\Response;

use Symfony\Component\HttpFoundation\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * @param mixed $data
     */
    public function __construct($data = null, int $status = 200, array $headers = [])
    {
        parent::__construct($data, $status, $headers);
    }

    public static function respond(string $message, int $status = 200): self
    {
        return new self($message, $status);
    }
}
