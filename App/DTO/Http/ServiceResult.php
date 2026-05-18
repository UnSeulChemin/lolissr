<?php

declare(strict_types=1);

namespace App\DTO\Http;

final readonly class ServiceResult
{
    public function __construct(
        public bool $success,
        public int $status,
        public string $message,
        public array $data = [],
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'message' => $this->message,
            ...$this->data,
        ];
    }
}