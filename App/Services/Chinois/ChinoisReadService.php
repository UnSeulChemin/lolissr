<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\Models\ChinoisVocabulaire;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;

final readonly class ChinoisReadService
{
    public function __construct(
        private ChinoisVocabulaireRepository $chinoisRepository,
    ) {
    }

    /**
     * @return list<ChinoisVocabulaire>
     */
    public function mandarin(): array
    {
        return $this->findByLangue(
            'mandarin',
        );
    }

    /**
     * @return list<ChinoisVocabulaire>
     */
    public function jinyu(): array
    {
        return $this->findByLangue(
            'jinyu',
        );
    }

    /**
     * @return list<ChinoisVocabulaire>
     */
    private function findByLangue(
        string $langue,
    ): array {
        return $this->chinoisRepository
            ->findByLangue($langue);
    }
}