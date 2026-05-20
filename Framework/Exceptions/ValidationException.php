<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class ValidationException extends HttpException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(
        private readonly array $errors,
        string $message = 'Erreur de validation',
    ) {
        parent::__construct(
            $message,
            422,
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
