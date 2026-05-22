<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class UnauthorizedException extends HttpException
{
    public function __construct(
        string $message = 'Non authentifié',
    ) {
        parent::__construct(
            $message,
            401,
        );
    }
}