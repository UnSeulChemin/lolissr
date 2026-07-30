<?php

declare(strict_types=1);

namespace Framework\Exceptions;

use Framework\Http\JsonResponse;

use Exception;

final class JsonResponseException extends Exception
{
    public function __construct(
        private readonly JsonResponse $response
    ) {
        parent::__construct(
            $this->resolveMessage($response),
            $response->status()
        );
    }

    // =========================================
    // RÉPONSE
    // =========================================

    public function response(): JsonResponse
    {
        return $this->response;
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    private function resolveMessage(JsonResponse $response): string
    {
        $message = $response->data()['message'] ?? null;

        return is_scalar($message)
            ? (string) $message
            : 'Erreur JSON';
    }
}