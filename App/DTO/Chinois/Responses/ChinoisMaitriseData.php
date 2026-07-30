<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Responses;

final readonly class ChinoisMaitriseData
{
    public function __construct(
        public bool $maitrise,
        public bool $xpEarned
    ) {
    }
}