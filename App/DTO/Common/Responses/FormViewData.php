<?php

declare(strict_types=1);

namespace App\DTO\Common\Responses;

readonly class FormViewData extends ViewData
{
    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $old
     */
    public function __construct(
        string $baseUri,
        public array $errors,
        public array $old,
        public string $formAction,
        public string $cancelUrl,
    ) {
        parent::__construct($baseUri);
    }
}