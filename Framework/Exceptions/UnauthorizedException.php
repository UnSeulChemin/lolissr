<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class UnauthorizedException extends HttpException
{
    public function __construct(
        string $message = 'Accès non autorisé',
    ) {
        parent::__construct(
            message: $message,
            statusCode: 401,
        );
    }
}