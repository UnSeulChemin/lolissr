<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class CsrfException extends HttpException
{
    public function __construct(
        string $message = 'Session expirée ou token CSRF invalide',
    ) {
        parent::__construct(
            message: $message,
            statusCode: 419,
        );
    }
}