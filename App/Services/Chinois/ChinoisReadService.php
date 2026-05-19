<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\Models\ChinoisVocabulaire;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;

final class ChinoisReadService
{
    public function __construct(
        private readonly ChinoisVocabulaireRepository $chinoisRepository
    ) {}

    /**
     * @return list<ChinoisVocabulaire>
     */
    public function mandarin(): array
    {
        return $this->chinoisRepository
            ->findByLangue('mandarin');
    }

    /**
     * @return list<ChinoisVocabulaire>
     */
    public function jinyu(): array
    {
        return $this->chinoisRepository
            ->findByLangue('jinyu');
    }
}