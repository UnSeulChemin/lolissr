<?php

declare(strict_types=1);

namespace App\DTO\Common\Responses;

readonly class FlashToastData
{
    public function __construct(
        public ?string $message,
        public ?string $type,
    ) {
    }
}