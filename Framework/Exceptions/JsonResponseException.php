<?php

declare(strict_types=1);

namespace Framework\Exceptions;

use Framework\Http\JsonResponse;
use Exception;

final class JsonResponseException extends Exception
{
    public function __construct(
        private readonly JsonResponse $response,
    ) {
        parent::__construct(
            (string) (
                $response->data()['message']
                ?? 'Erreur JSON'
            ),
            $response->getStatusCode(),
        );
    }

    public function response(): JsonResponse
    {
        return $this->response;
    }
}