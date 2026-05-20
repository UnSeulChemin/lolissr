<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Page introuvable')
    {
        parent::__construct($message, 404);
    }
}
