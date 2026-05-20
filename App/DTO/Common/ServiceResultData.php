<?php

declare(strict_types=1);

namespace App\DTO\Common;

final readonly class ServiceResultData
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public bool $success,
        public int $status,
        public string $message,
        public array $data = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
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
