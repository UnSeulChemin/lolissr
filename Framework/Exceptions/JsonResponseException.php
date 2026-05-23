<?php
declare(strict_types=1);

namespace Framework\Exceptions;

use Framework\Http\JsonResponse;

final class JsonResponseException extends \Exception
{
    private JsonResponse $response;

    public function __construct(JsonResponse $response)
    {
        $this->response = $response;
        parent::__construct($response->data()['message'] ?? 'Erreur JSON', $response->getStatusCode());
    }

    public function getResponse(): JsonResponse
    {
        return $this->response;
    }

    // 🔧 Méthode manquante pour ErrorHandler
    public function response(): JsonResponse
    {
        return $this->response;
    }
}