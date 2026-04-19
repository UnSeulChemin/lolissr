<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

class HttpException extends \Exception
{
    public function __construct(
        string $message = 'Erreur HTTP',
        private readonly int $statusCode = 500
    )
    {
        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}