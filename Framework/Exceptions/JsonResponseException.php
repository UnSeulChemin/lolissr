<?php

declare(strict_types=1);

namespace Framework\Exceptions;

use Framework\Http\JsonResponse;

final class JsonResponseException extends BaseHttpException
{
    public function __construct(
        private readonly JsonResponse $response,
    ) {
        // On met le status et message à 200 par défaut mais ça peut être personnalisé
        parent::__construct(
            message: 'JSON Response',
            statusCode: $this->response->getStatusCode(),
            data: $this->response->getData(),
            headers: $this->response->getHeaders()
        );
    }

    public function response(): JsonResponse
    {
        return $this->response;
    }
}