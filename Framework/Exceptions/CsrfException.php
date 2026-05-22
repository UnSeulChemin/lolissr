<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class CsrfException
    extends BaseHttpException
{
    public function __construct(
        string $message = 'Token CSRF invalide',
    ) {
        parent::__construct(
            message: $message,
            statusCode: 419,
        );
    }
}