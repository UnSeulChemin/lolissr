<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class AlreadyAuthenticatedException extends BaseHttpException
{
    public function __construct(string $message = 'Utilisateur déjà authentifié')
    {
        parent::__construct(
            message: $message,
            statusCode: 409
        );
    }
}