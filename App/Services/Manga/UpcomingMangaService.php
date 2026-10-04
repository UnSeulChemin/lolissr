<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Manga\Responses\UpcomingMangaData;

use Framework\Config\ApplicationConfig;

final class UpcomingMangaService
{
    public function __construct(private readonly \App\Repositories\Manga\MangaRepository $repository, private readonly ?string $catalogPath = null)
    {}

    /** @return list<UpcomingMangaData> */
    public function forSeries(string $slug): array
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
        $entries = $series[$slug]['upcoming'] ?? [];
        if (!is_array($entries)) return [];
        $owned = $entries === [] ? [] : $this->repository->ownedNumbers($slug);
        $result = [];
        foreach (array_slice($entries, 0, 24) as $entry)
        {
            if (!is_array($entry)) continue;
            $number = $entry['number'] ?? null;
            $date = $entry['release_date'] ?? null;
            $id = $entry['id'] ?? null;
            if (!is_int($number) || $number < 1 || in_array($number, $owned, true) || !is_string($date) || !is_string($id)) continue;
            if (preg_match('/^[a-f0-9-]{36}$/D', $id) !== 1) continue;
            $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if ($parsed === false || $parsed->format('Y-m-d') !== $date || $date <= date('Y-m-d')) continue;
            $image = 'images/manga/upcoming/' . $id . '.jpg';
            $result[] = new UpcomingMangaData(
                $number, $date, $parsed->format('d/m/Y'),
                'https://www.mangacollec.com/volumes/' . $id,
                is_file(base_path('public/' . $image)) ? ApplicationConfig::baseUri() . $image : null
            );
        }
        usort($result, static fn (UpcomingMangaData $a, UpcomingMangaData $b): int => [$a->date, $a->number] <=> [$b->date, $b->number]);
        return $result;
    }
}
