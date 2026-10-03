<?php

declare(strict_types=1);

namespace Framework\Http\Exceptions;

final class NotFoundException extends BaseHttpException
{
    public function __construct(string $message = 'Page introuvable')
    {
        parent::__construct(message: $message, statusCode: 404);
    }
}