<?php
declare(strict_types=1);

namespace Framework\Exceptions;

use Framework\Http\JsonResponse;

final class JsonResponseException extends \Exception
{
    public function __construct(private JsonResponse $response)
    {
        parent::__construct($response->data()['message'] ?? 'Erreur JSON', $response->getStatusCode());
    }

    public function getResponse(): JsonResponse
    {
        return $this->response;
    }
}