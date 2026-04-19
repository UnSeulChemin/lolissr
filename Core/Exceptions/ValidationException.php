<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

final class ValidationException extends HttpException
{
    public function __construct(
        private readonly array $errors,
        string $message = 'Erreur de validation'
    )
    {
        parent::__construct($message, 422);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}