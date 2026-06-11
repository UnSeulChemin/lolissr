<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class ForbiddenException extends BaseHttpException
{
    public function __construct(
        string $message = 'Accès interdit',
    ) {
        parent::__construct(
            message: $message,
            statusCode: 403,
        );
    }
}