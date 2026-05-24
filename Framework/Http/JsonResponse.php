<?php

declare(strict_types=1);

namespace Framework\Http;

final readonly class JsonResponse
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private array $data,
        private int $status = 200,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function send(): never
    {
        Response::json(
            $this->data,
            $this->status,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function success(
        array $data = [],
        int $status = 200,
    ): self {

        return new self(
            [
                'success' => true,
                ...$data,
            ],
            $status,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function error(
        string $message,
        int $status = 400,
        array $data = [],
    ): self {

        return new self(
            [
                'success' => false,
                'message' => $message,
                ...$data,
            ],
            $status,
        );
    }
}