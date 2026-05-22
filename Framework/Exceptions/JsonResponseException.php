<?php

declare(strict_types=1);

namespace Framework\Exceptions;

use Exception;
use Framework\Http\JsonResponse;

final class JsonResponseException extends Exception
{
    public function __construct(
        private readonly JsonResponse $response,
    ) {
        parent::__construct();
    }

    public function response(): JsonResponse
    {
        return $this->response;
    }
}