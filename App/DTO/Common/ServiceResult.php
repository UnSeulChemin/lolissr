<?php

declare(strict_types=1);

namespace App\DTO\Common;

final readonly class ServiceResult
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        public bool $success,
        public int $status,
        public string $message,
        public array $data = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function success(
        string $message = 'Succès',
        array $data = [],
        int $status = 200,
    ): self {
        return new self(
            success: true,
            status: $status,
            message: $message,
            data: $data,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function error(
        string $message = 'Erreur',
        array $data = [],
        int $status = 400,
    ): self {
        return new self(
            success: false,
            status: $status,
            message: $message,
            data: $data,
        );
    }

    /**
     * @return array{
     *     success: bool,
     *     status: int,
     *     message: string,
     *     data: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}