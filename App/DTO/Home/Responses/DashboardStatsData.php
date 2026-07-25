<?php

declare(strict_types=1);

namespace App\DTO\Home\Responses;

use App\DTO\Manga\Responses\ArtbookRepresentationData;
use App\DTO\Manga\Responses\ArtbookStatsData;
use App\DTO\Manga\Responses\MangaStatsData;

final readonly class DashboardStatsData
{
    /**
     * @param list<MangaStatsData> $topLongestSeries
     * @param list<MangaStatsData> $lowRatedMangas
     * @param list<MangaStatsData> $lowJacquetteMangas
     * @param list<MangaStatsData> $lowLivreStateMangas
     */
    public function __construct(
        // Chinois
        public int $totalVocabulary,
        public int $remainingVocabulary,
        public int $learnedVocabulary,
        public int $vocabularyProgress,

        public int $totalGrammar,
        public int $remainingGrammar,
        public int $learnedGrammar,
        public int $grammarProgress,

        public int $globalChineseProgress,
        public string $globalChineseProgressLabel,

        // Manga
        public int $totalTomes,
        public int $totalSeries,

        public int $totalRead,
        public int $totalUnread,
        public int $readingProgress,

        public ?float $averageNote,
        public string $averageNoteLabel,

        public ?MangaStatsData $lastTome,
        public ?MangaStatsData $longestSeries,

        public array $topLongestSeries,
        public array $lowRatedMangas,
        public array $lowJacquetteMangas,
        public array $lowLivreStateMangas,

        // Artbooks
        public int $totalArtbooks,
        public int $totalArtbookAuthors,
        public int $totalArtbookSeries,

        public ?ArtbookStatsData $latestArtbook,
        public ?ArtbookRepresentationData $mostRepresented,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            // Chinois
            'totalVocabulary' => $this->totalVocabulary,
            'remainingVocabulary' => $this->remainingVocabulary,
            'learnedVocabulary' => $this->learnedVocabulary,
            'vocabularyProgress' => $this->vocabularyProgress,

            'totalGrammar' => $this->totalGrammar,
            'remainingGrammar' => $this->remainingGrammar,
            'learnedGrammar' => $this->learnedGrammar,
            'grammarProgress' => $this->grammarProgress,

            'globalChineseProgress' => $this->globalChineseProgress,
            'globalChineseProgressLabel' => $this->globalChineseProgressLabel,

            // Manga
            'totalTomes' => $this->totalTomes,
            'totalSeries' => $this->totalSeries,

            'totalRead' => $this->totalRead,
            'totalUnread' => $this->totalUnread,
            'readingProgress' => $this->readingProgress,

            'averageNote' => $this->averageNote,
            'averageNoteLabel' => $this->averageNoteLabel,

            'lastTome' => $this->lastTome?->toArray(),
            'longestSeries' => $this->longestSeries?->toArray(),

            'topLongestSeries' => array_map(
                static fn (MangaStatsData $manga): array => $manga->toArray(),
                $this->topLongestSeries
            ),

            'lowRatedMangas' => array_map(
                static fn (MangaStatsData $manga): array => $manga->toArray(),
                $this->lowRatedMangas
            ),

            'lowJacquetteMangas' => array_map(
                static fn (MangaStatsData $manga): array => $manga->toArray(),
                $this->lowJacquetteMangas
            ),

            'lowLivreStateMangas' => array_map(
                static fn (MangaStatsData $manga): array => $manga->toArray(),
                $this->lowLivreStateMangas
            ),

            // Artbooks
            'totalArtbooks' => $this->totalArtbooks,
            'totalArtbookAuthors' => $this->totalArtbookAuthors,
            'totalArtbookSeries' => $this->totalArtbookSeries,

            'latestArtbook' => $this->latestArtbook?->toArray(),
            'mostRepresented' => $this->mostRepresented?->toArray(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            // Chinois
            totalVocabulary: (int) $data['totalVocabulary'],
            remainingVocabulary: (int) $data['remainingVocabulary'],
            learnedVocabulary: (int) $data['learnedVocabulary'],
            vocabularyProgress: (int) $data['vocabularyProgress'],

            totalGrammar: (int) $data['totalGrammar'],
            remainingGrammar: (int) $data['remainingGrammar'],
            learnedGrammar: (int) $data['learnedGrammar'],
            grammarProgress: (int) $data['grammarProgress'],

            globalChineseProgress: (int) $data['globalChineseProgress'],
            globalChineseProgressLabel: (string) $data['globalChineseProgressLabel'],

            // Manga
            totalTomes: (int) $data['totalTomes'],
            totalSeries: (int) $data['totalSeries'],

            totalRead: (int) $data['totalRead'],
            totalUnread: (int) $data['totalUnread'],
            readingProgress: (int) $data['readingProgress'],

            averageNote: isset($data['averageNote'])
                ? (float) $data['averageNote']
                : null,

            averageNoteLabel: (string) $data['averageNoteLabel'],

            lastTome: is_array($data['lastTome'] ?? null)
                ? MangaStatsData::fromArray($data['lastTome'])
                : null,

            longestSeries: is_array($data['longestSeries'] ?? null)
                ? MangaStatsData::fromArray($data['longestSeries'])
                : null,

            topLongestSeries: self::hydrateMangaList(
                $data['topLongestSeries'] ?? []
            ),

            lowRatedMangas: self::hydrateMangaList(
                $data['lowRatedMangas'] ?? []
            ),

            lowJacquetteMangas: self::hydrateMangaList(
                $data['lowJacquetteMangas'] ?? []
            ),

            lowLivreStateMangas: self::hydrateMangaList(
                $data['lowLivreStateMangas'] ?? []
            ),

            // Artbooks
            totalArtbooks: (int) $data['totalArtbooks'],
            totalArtbookAuthors: (int) $data['totalArtbookAuthors'],
            totalArtbookSeries: (int) $data['totalArtbookSeries'],

            latestArtbook: is_array($data['latestArtbook'] ?? null)
                ? ArtbookStatsData::fromArray($data['latestArtbook'])
                : null,

            mostRepresented: is_array($data['mostRepresented'] ?? null)
                ? ArtbookRepresentationData::fromArray($data['mostRepresented'])
                : null,
        );
    }

    /**
     * @return list<MangaStatsData>
     */
    private static function hydrateMangaList(mixed $data): array
    {
        if (! is_array($data))
        {
            return [];
        }

        $mangas = [];

        foreach ($data as $item)
        {
            if (! is_array($item))
            {
                continue;
            }

            $mangas[] = MangaStatsData::fromArray($item);
        }

        return $mangas;
    }
}