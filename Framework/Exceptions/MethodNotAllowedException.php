<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class MethodNotAllowedException extends BaseHttpException
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        string $message = 'Méthode non autorisée',
        array $headers = []
    ) {
        parent::__construct(
            message: $message,
            statusCode: 405,
            headers: $headers
        );
    }
}