<?php

declare(strict_types=1);

namespace App\DTO\Common\Responses;

final readonly class FormViewData
{
    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $old
     */
    public function __construct(
        public array $errors,
        public array $old,
        public string $formAction,
        public string $cancelUrl,
    ) {
    }
}