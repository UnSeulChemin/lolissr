<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\DTO\Chinois\Responses\ChinoisCategorieData;
use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\DTO\Chinois\Responses\ChinoisHskData;
use App\DTO\Chinois\Responses\ChinoisSearchData;
use App\DTO\Chinois\Responses\ChinoisSectionData;
use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\DTO\Chinois\Responses\ChinoisVocabulairePageData;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisSearchRepository;
use App\Repositories\Chinois\ChinoisVocabulaireCollectionRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;

use Framework\Application\App;
use Framework\Exceptions\NotFoundException;

final readonly class ChinoisReadService
{
    /**
     * @var array<string, array{
     *     description: string,
     *     sourceUrl: string,
     *     sourceDescription: string
     * }>
     */
    private const HSK = [
        'HSK1' => [
            'description' => 'Structures courantes, phrases du quotidien et grammaire HSK1.',
            'sourceUrl' => 'https://chine.in/mandarin/grammaire/RGLA1',
            'sourceDescription' => 'Références, structures et exemples de grammaire chinoise pour débutants.',
        ],
        'HSK2' => [
            'description' => 'Structures courantes, phrases du quotidien et grammaire HSK2.',
            'sourceUrl' => 'https://chine.in/mandarin/grammaire/RGLA2',
            'sourceDescription' => 'Références, structures et exemples de grammaire chinoise pour débutants intermédiaires.',
        ],
        'HSK3' => [
            'description' => 'Structures intermédiaires, phrases naturelles et grammaire HSK3.',
            'sourceUrl' => 'https://chine.in/mandarin/grammaire/RGLB1',
            'sourceDescription' => 'Références, structures et exemples de grammaire chinoise intermédiaire.',
        ],
        'HSK4' => [
            'description' => 'Structures avancées, nuances et grammaire HSK4.',
            'sourceUrl' => 'https://chine.in/mandarin/grammaire/RGLB2',
            'sourceDescription' => 'Références, structures et exemples de grammaire chinoise avancée.',
        ],
    ];

    private const LANGUES = ['mandarin', 'jinyu'];

    public function __construct(
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private ChinoisVocabulaireCollectionRepository $collectionRepository,
        private ChinoisGrammaireRepository $grammaireRepository,
        private ChinoisSearchRepository $searchRepository
    ) {
    }

    // =========================================
    // GRAMMAIRE
    // =========================================

    public function hsk(string $niveau): ChinoisHskData
    {
        $niveau = mb_strtoupper(trim($niveau));
        $config = self::HSK[$niveau] ?? throw new NotFoundException('Niveau HSK introuvable');

        return new ChinoisHskData(
            level: str_replace('HSK', '', $niveau),
            description: $config['description'],
            sourceUrl: $config['sourceUrl'],
            sourceDescription: $config['sourceDescription'],
            sections: $this->buildSections($this->grammaireRepository->findByLevel($niveau))
        );
    }

    public function grammaire(string $niveau, int $id): ?ChinoisGrammaireData
    {
        return $this->grammaireRepository->findByNiveauAndId(
            mb_strtoupper(trim($niveau)),
            $id
        );
    }

    // =========================================
    // VOCABULAIRE
    // =========================================

    public function langue(string $langue, int|string $page = 1): ?ChinoisVocabulairePageData
    {
        $langue = mb_strtolower(trim($langue));

        if (! in_array($langue, self::LANGUES, true))
        {
            return null;
        }

        $page = max(1, (int) $page);
        $perPage = App::pagination();
        $totalVocabulaires = $this->collectionRepository->countByLangue($langue);

        if ($totalVocabulaires === 0)
        {
            return new ChinoisVocabulairePageData(
                vocabulaires: [],
                currentPage: 1,
                totalVocabulaires: 0,
                perPage: $perPage,
                totalPages: 1
            );
        }

        $totalPages = (int) ceil($totalVocabulaires / $perPage);

        if ($page > $totalPages)
        {
            return null;
        }

        return new ChinoisVocabulairePageData(
            vocabulaires: $this->collectionRepository->findByLanguePaginated(
                $langue,
                $perPage,
                $page
            ),
            currentPage: $page,
            totalVocabulaires: $totalVocabulaires,
            perPage: $perPage,
            totalPages: $totalPages
        );
    }

    public function vocabulaire(string $langue, int $id): ?ChinoisVocabulaireData
    {
        return $this->vocabulaireRepository->findByLangueAndId(
            mb_strtolower(trim($langue)),
            $id
        );
    }

    // =========================================
    // FLASHCARDS
    // =========================================

    /**
     * @return list<ChinoisGrammaireData>
     */
    public function grammaireFlashcards(): array
    {
        return $this->grammaireRepository->findNotMasteredDto();
    }

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function vocabulaireFlashcards(): array
    {
        return $this->vocabulaireRepository->findNotMasteredDto();
    }

    // =========================================
    // RECHERCHE
    // =========================================

    public function search(string $query = ''): ChinoisSearchData
    {
        $query = trim($query);

        return new ChinoisSearchData(
            results: $this->searchRepository->search($query),
            search: $query
        );
    }

    // =========================================
    // CONSTRUCTION
    // =========================================

    /**
     * @param list<ChinoisGrammaireData> $grammaires
     * @return list<ChinoisSectionData>
     */
    private function buildSections(array $grammaires): array
    {
        $sections = [];

        foreach ($grammaires as $grammaire)
        {
            $sections[$grammaire->section][$grammaire->categorie][] = $grammaire;
        }

        $results = [];

        foreach ($sections as $section => $categories)
        {
            $results[] = new ChinoisSectionData(
                title: $section,
                id: $this->slugify($section),
                categories: $this->buildCategories($categories)
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
                grammaires: $grammaires
            );
        }

        return $results;
    }

    private function slugify(string $value): string
    {
        $slug = transliterator_transliterate('Any-Latin; Latin-ASCII', $value);

        if ($slug === false)
        {
            $slug = $value;
        }

        $slug = mb_strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';

        return trim($slug, '-');
    }
}