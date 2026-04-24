<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application\App;
use App\Core\Support\Str;
use App\Repositories\MangaRepository;

final class MangaReadService
{
    public function __construct(
        private readonly MangaRepository $mangaRepository
    ) {}

    public function repository(): MangaRepository
    {
        return $this->mangaRepository;
    }

    private function normalizeSearchQuery(string $query): string
    {
        $query = urldecode($query);
        $query = str_replace('-', ' ', $query);

        return trim(preg_replace('/\s+/', ' ', $query) ?? '');
    }

    public function collection(string $page = '1'): ?array
    {
        if (!ctype_digit($page)) {
            return null;
        }

        $currentPage = (int) $page;

        if ($currentPage < 1) {
            return null;
        }

        $pagination = App::pagination();

        $compteur = $this->mangaRepository
            ->countFirstTomesPaginate($pagination);

        if ($compteur > 0 && $currentPage > $compteur) {
            return null;
        }

        $mangas = $this->mangaRepository
            ->findAllFirstTomes('id DESC', $pagination, $currentPage);

        return [
            'mangas' => $mangas,
            'compteur' => $compteur,
            'currentPage' => $currentPage,
        ];
    }

    public function search(string $query = ''): array
    {
        $search = $this->normalizeSearchQuery($query);

        if ($search === '') {
            return [
                'mangas' => [],
                'search' => '',
            ];
        }

        return [
            'mangas' => $this->mangaRepository->searchMangas($search),
            'search' => $search,
        ];
    }

    public function searchAjax(string $query = ''): array
    {
        $search = $this->normalizeSearchQuery($query);

        if ($search === '') {
            return [];
        }

        $mangas = $this->mangaRepository->searchMangas($search);
        $results = [];

        foreach (array_slice($mangas, 0, 6) as $manga) {
            if (!isset($manga->slug)) {
                continue;
            }

            $results[] = [
                'slug' => $manga->slug,
                'numero' => (int) $manga->numero,
                'livre' => $manga->livre,
                'thumbnail' => $manga->thumbnail,
                'extension' => $manga->extension,
                'note' => $manga->note !== null ? (int) $manga->note : null,
            ];
        }

        return $results;
    }

    public function serie(string $slug): ?array
    {
        $normalizedSlug = Str::slug($slug);

        $mangas = $this->mangaRepository->findBySlug($normalizedSlug);

        if ($mangas === []) {
            return null;
        }

        return [
            'mangas' => $mangas,
            'canonicalSlug' => $normalizedSlug,
        ];
    }

    public function one(string $slug, int $numero): ?array
    {
        $normalizedSlug = Str::slug($slug);

        $manga = $this->mangaRepository
            ->findOneBySlugAndNumero($normalizedSlug, $numero);

        if ($manga === false) {
            return null;
        }

        return [
            'manga' => $manga,
            'canonicalSlug' => $normalizedSlug,
        ];
    }
}