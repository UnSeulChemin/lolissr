<?php

declare(strict_types=1);

namespace App\DTO\Common\Responses;

readonly class ViewData
{
    public function __construct(
        public string $baseUri,
    ) {
    }
}