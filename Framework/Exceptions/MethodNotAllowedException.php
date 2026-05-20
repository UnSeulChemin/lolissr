<?php

declare(strict_types=1);

namespace Framework\Exceptions;

final class MethodNotAllowedException extends HttpException
{
    public function __construct(string $message = 'Méthode non autorisée')
    {
        parent::__construct($message, 405);
    }
}
