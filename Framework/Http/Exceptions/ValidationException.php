<?php

declare(strict_types=1);

namespace Framework\Http\Exceptions;

final class ValidationException extends BaseHttpException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(
        array $errors,
        string $message = 'Erreur de validation',
    ) {
        parent::__construct(
            message: $message,
            statusCode: 422,
            data: [
                'errors' => $errors,
            ]
        );
    }
}