<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\DTO\Chinois\Responses\ChinoisCategorieData;
use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\DTO\Chinois\Responses\ChinoisHskData;
use App\DTO\Chinois\Responses\ChinoisSearchData;
use App\DTO\Chinois\Responses\ChinoisSectionData;
use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisSearchRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;

final readonly class ChinoisReadService
{
    private const DESCRIPTIONS = [
        'HSK1' => 'Structures courantes, phrases du quotidien et grammaire HSK1.',
        'HSK2' => 'Structures courantes, phrases du quotidien et grammaire HSK2.',
        'HSK3' => 'Structures intermédiaires, phrases naturelles et grammaire HSK3.',
        'HSK4' => 'Structures avancées, nuances et grammaire HSK4.',
    ];

    private const SOURCES = [
        'HSK1' => 'https://chine.in/mandarin/grammaire/RGLA1',
        'HSK2' => 'https://chine.in/mandarin/grammaire/RGLA2',
        'HSK3' => 'https://chine.in/mandarin/grammaire/RGLB1',
        'HSK4' => 'https://chine.in/mandarin/grammaire/RGLB2',
    ];

    private const SOURCE_DESCRIPTIONS = [
        'HSK1' => 'Références, structures et exemples de grammaire chinoise pour débutants.',
        'HSK2' => 'Références, structures et exemples de grammaire chinoise pour débutants intermédiaires.',
        'HSK3' => 'Références, structures et exemples de grammaire chinoise intermédiaire.',
        'HSK4' => 'Références, structures et exemples de grammaire chinoise avancée.',
    ];

    public function __construct(
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private ChinoisGrammaireRepository $grammaireRepository,
        private ChinoisSearchRepository $searchRepository,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | GRAMMAIRE
    |--------------------------------------------------------------------------
    */

    public function hsk(string $niveau): ChinoisHskData
    {
        $grammaires = $this->grammaireRepository->findByLevel($niveau);

        return new ChinoisHskData(
            level: substr($niveau, 3),

            description:
                self::DESCRIPTIONS[$niveau]
                ?? '',

            sourceUrl:
                self::SOURCES[$niveau]
                ?? '',

            sourceDescription:
                self::SOURCE_DESCRIPTIONS[$niveau]
                ?? '',

            sections:
                $this->buildSections($grammaires),
        );
    }

    public function grammaire(int $id): ?ChinoisGrammaireData
    {
        return $this->grammaireRepository->findById($id);
    }

    /*
    |--------------------------------------------------------------------------
    | VOCABULAIRE
    |--------------------------------------------------------------------------
    */

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function mandarin(): array
    {
        return $this->vocabulaireRepository->findByLangue('mandarin');
    }

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function jinyu(): array
    {
        return $this->vocabulaireRepository->findByLangue('jinyu');
    }

    public function vocabulaire(int $id): ?ChinoisVocabulaireData
    {
        return $this->vocabulaireRepository->findById($id);
    }

    /*
    |--------------------------------------------------------------------------
    | FLASHCARDS
    |--------------------------------------------------------------------------
    */

    /**
     * @return list<ChinoisGrammaireData>
     */
    public function flashcardsGrammaire(): array
    {
        return $this->grammaireRepository->findNotMasteredDto();
    }

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function flashcardsVocabulaire(): array
    {
        return $this->vocabulaireRepository->findNotMasteredDto();
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(string|int $query = ''): ChinoisSearchData
    {
        $query = trim((string) $query);

        return new ChinoisSearchData(
            results: $this->searchRepository->search($query),
            search: $query,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * @param list<ChinoisGrammaireData> $grammaires
     * @return list<ChinoisSectionData>
     */
    private function buildSections(array $grammaires): array
    {
        $sections = [];

        foreach ($grammaires as $grammaire)
        {
            $sections[$grammaire->section][$grammaire->categorie][] =
                $grammaire;
        }

        $results = [];

        foreach ($sections as $section => $categories)
        {
            $results[] = new ChinoisSectionData(
                title: $section,
                id: $this->slugify($section),
                categories: $this->buildCategories($categories),
            );
        }

        return $results;
    }

    /**
     * @param array<string, list<ChinoisGrammaireData>> $categories
     * @return list<ChinoisCategorieData>
     */
    private function buildCategories(array $categories): array
    {
        $results = [];

        foreach ($categories as $categorie => $grammaires)
        {
            $results[] = new ChinoisCategorieData(
                title: $categorie,
                grammaires: $grammaires,
            );
        }

        return $results;
    }

    private function slugify(string $value): string
    {
        $slug = transliterator_transliterate(
            'Any-Latin; Latin-ASCII',
            $value,
        );

        if ($slug === false)
        {
            $slug = $value;
        }

        $slug = mb_strtolower($slug);

        $slug = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $slug,
        ) ?? '';

        return trim(
            $slug,
            '-',
        );
    }
}