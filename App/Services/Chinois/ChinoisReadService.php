<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\Repositories\Chinois\ChinoisRepository;

final class ChinoisReadService
{
    public function __construct(
        private readonly ChinoisRepository $chinoisRepository
    ) {}

    public function mandarin(): array
    {
        return $this->chinoisRepository
            ->findByLangue('mandarin');
    }

    public function jin(): array
    {
        return $this->chinoisRepository
            ->findByLangue('jinyu');
    }
}