<?php

declare(strict_types=1);

namespace Framework\Exceptions;

use Exception;

class BaseHttpException extends Exception
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function __construct(
        string $message = 'HTTP Error',
        private readonly int $statusCode = 500,
        private readonly array $data = [],
        private readonly array $headers = [],
    ) {
        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
