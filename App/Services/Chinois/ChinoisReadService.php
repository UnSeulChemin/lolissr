<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\DTO\Chinois\Responses\ChinoisSearchData;
use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisSearchRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;

final readonly class ChinoisReadService
{
    public function __construct(
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private ChinoisGrammaireRepository $grammaireRepository,
        private ChinoisSearchRepository $searchRepository,
    ) {
    }

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function mandarin(): array
    {
        return $this->vocabulaireRepository
            ->findByLangue('mandarin');
    }

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function jinyu(): array
    {
        return $this->vocabulaireRepository
            ->findByLangue('jinyu');
    }

    /**
     * @return list<ChinoisGrammaireData>
     */
    public function hsk(
        string $niveau,
    ): array {
        return $this->grammaireRepository
            ->findByLevel($niveau);
    }

    public function grammaire(
        int $id,
    ): ?ChinoisGrammaireData {
        return $this->grammaireRepository
            ->findById($id);
    }

    public function vocabulaire(
        int $id,
    ): ?ChinoisVocabulaireData {
        return $this->vocabulaireRepository
            ->findById($id);
    }

    /**
     * @return list<ChinoisGrammaireData>
     */
    public function flashcardsGrammaire(): array
    {
        return $this->grammaireRepository
            ->findNotMasteredDto();
    }

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function flashcardsVocabulaire(): array
    {
        return $this->vocabulaireRepository
            ->findNotMasteredDto();
    }

    public function search(
        string $query = '',
    ): ChinoisSearchData {

        $query =
            trim($query);

        return new ChinoisSearchData(
            results:
                $this->searchRepository
                    ->search($query),

            search:
                $query,
        );
    }
}