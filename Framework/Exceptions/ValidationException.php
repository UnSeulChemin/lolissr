<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class ValidationException extends BaseHttpException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(
        private readonly array $errors,
        string $message = 'Erreur de validation',
    ) {
        parent::__construct(
            message: $message,
            statusCode: 422,
            data: ['errors' => $errors],
        );
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
