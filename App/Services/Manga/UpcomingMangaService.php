<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Manga\Responses\UpcomingMangaData;

use Framework\Config\ApplicationConfig;

final class UpcomingMangaService
{
    /** @var array<string, list<int>> */
    private array $collectionNumbers = [];

    /** @var array<string, mixed>|null */
    private ?array $catalog = null;

    /** @return list<UpcomingMangaData> */
    public function all(): array
    {
        $result = iterator_to_array($this->collectionReleases(), false);
        usort($result, static fn (UpcomingMangaData $a, UpcomingMangaData $b): int => [$a->date, $a->title, $a->number] <=> [$b->date, $b->title, $b->number]);
        return $result;
    }

    /** @return array{upcomingCount: int, missingCount: int, nextRelease: ?UpcomingMangaData} */
    public function summary(): array
    {
        $upcoming = 0;
        $missing = 0;
        $next = null;
        foreach ($this->collectionReleases() as $release)
        {
            if (!$release->isUpcoming) { $missing++; continue; }
            $upcoming++;
            if ($next === null || [$release->date, $release->title, $release->number] < [$next->date, $next->title, $next->number]) $next = $release;
        }
        return ['upcomingCount' => $upcoming, 'missingCount' => $missing, 'nextRelease' => $next];
    }

    /** @return \Generator<int, UpcomingMangaData> */
    private function collectionReleases(): \Generator
    {
        $titles = [];
        $this->collectionNumbers = [];
        foreach ($this->repository->releaseCollection() as $row)
        {
            $titles[$row['slug']] = $row['livre'];
            $this->collectionNumbers[$row['slug']][] = $row['numero'];
        }
        $this->catalog = $this->loadCatalog();
        try
        {
            foreach ($titles as $slug => $title)
                foreach ($this->seriesEntries($slug) as $release)
                    yield new UpcomingMangaData($release->number, $release->date, $release->dateLabel, $release->sourceUrl, $release->imageUrl, $release->isUpcoming, $slug, $title);
        }
        finally
        {
            $this->collectionNumbers = [];
            $this->catalog = null;
        }
    }
    public function __construct(private readonly \App\Repositories\Manga\MangaRepository $repository, private readonly ?string $catalogPath = null)
    {}

    /** @return list<UpcomingMangaData> */
    public function forSeries(string $slug, int $page = 1, ?int $perPage = null): array
    {
        $result = iterator_to_array($this->seriesEntries($slug, $page, $perPage), false);
        usort($result, static fn (UpcomingMangaData $a, UpcomingMangaData $b): int => [$b->number, $b->date] <=> [$a->number, $a->date]);
        return $result;
    }

    /** @return array<string, mixed> */
    private function loadCatalog(): array
    {
        $path = $this->catalogPath ?? base_path('storage/manga-releases.json');
        $owner = user();
        if ($owner === null || !is_file($path)) return [];
        $contents = @file_get_contents($path);
        if ($contents === false) return [];
        $catalog = json_decode($contents, true);
        if (!is_array($catalog)) return [];
        $users = $catalog['users'] ?? null;
        if (!is_array($users)) return [];
        $series = $users[(string) $owner->id] ?? null;
        if (!is_array($series)) return [];
        return $series;
    }

    /** @return \Generator<int, UpcomingMangaData> */
    private function seriesEntries(string $slug, int $page = 1, ?int $perPage = null): \Generator
    {
        $series = $this->catalog ?? $this->loadCatalog();
        $entries = $series[$slug]['upcoming'] ?? [];
        if (!is_array($entries)) return;
        $owned = $entries === [] ? [] : ($this->collectionNumbers[$slug] ?? $this->repository->ownedNumbers($slug));
        $upper = PHP_INT_MAX;
        $lower = 0;
        if ($perPage !== null && $perPage > 0)
        {
            rsort($owned, SORT_NUMERIC);
            $offset = (max(1, $page) - 1) * $perPage;
            $upper = $offset > 0 ? ($owned[$offset - 1] ?? 0) : PHP_INT_MAX;
            $lower = count($owned) > $offset + $perPage ? ($owned[$offset + $perPage - 1] ?? 0) : 0;
        }
        foreach (array_slice($entries, 0, 500) as $entry)
        {
            if (!is_array($entry)) continue;
            $number = $entry['number'] ?? null;
            $date = $entry['release_date'] ?? null;
            $id = $entry['id'] ?? null;
            if (!is_int($number) || $number < 1 || $number >= $upper || $number < $lower || in_array($number, $owned, true) || !is_string($date) || !is_string($id)) continue;
            if (preg_match('/^[a-f0-9-]{36}$/D', $id) !== 1) continue;
            $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if ($parsed === false || $parsed->format('Y-m-d') !== $date) continue;
            $image = 'images/manga/upcoming/' . $id . '.jpg';
            yield new UpcomingMangaData(
                $number, $date, $parsed->format('d/m/Y'),
                'https://www.mangacollec.com/volumes/' . $id,
                is_file(base_path('public/' . $image)) ? ApplicationConfig::baseUri() . $image : null, $date > date('Y-m-d')
            );
        }
    }
}
