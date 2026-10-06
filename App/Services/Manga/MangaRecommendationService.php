<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Repositories\Manga\MangaRepository;

final class MangaRecommendationService
{
    public function __construct(private readonly MangaRepository $repository, private readonly ?string $catalogPath = null, private readonly ?string $hiddenPath = null)
    {}

    public static function normalize(string $title): string
    {
        $title = transliterator_transliterate('Any-Latin; Latin-ASCII', mb_strtolower($title));
        return preg_replace('/[^a-z0-9]+/', '', $title === false ? '' : $title) ?? '';
    }

    /** @return list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}> */
    public function all(string $mode = 'categories'): array
    {
        if (user() === null) return [];
        $path = $this->catalogPath ?? base_path('storage/manga-recommendations.json');
        $contents = is_file($path) ? @file_get_contents($path) : false;
        $catalog = $contents === false ? null : json_decode($contents, true);
        if (!is_array($catalog)) return [];
        $titles = array_column($this->repository->releaseCollection(), 'livre');
        return self::fromCatalog($catalog, $titles, $mode, $this->hidden());
    }

    /**
     * @param array<mixed> $catalog
     * @param list<string> $titles
     * @param list<string> $hidden
     * @return list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}>
     */
    public static function fromCatalog(array $catalog, array $titles, string $mode = 'categories', array $hidden = []): array
    {
        $series = $catalog['series'] ?? null;
        $kinds = $catalog['kinds'] ?? null;
        if (!is_array($series) || !is_array($kinds)) return [];
        $owned = [];
        foreach ($titles as $title) $owned[self::normalize($title)] = true;
        $index = [];
        $ownedIds = [];
        foreach ($series as $entry)
        {
            if (!is_array($entry) || !is_string($entry['id'] ?? null) || !is_string($entry['title'] ?? null)) continue;
            if (preg_match('/^[a-f0-9-]{36}$/D', $entry['id']) !== 1) continue;
            $index[$entry['id']] = ['title' => $entry['title'], 'adult' => ($entry['adult_content'] ?? false) !== false];
            if (isset($owned[self::normalize($entry['title'])])) $ownedIds[$entry['id']] = true;
        }
        if ($mode === 'authors')
        {
            $authors = $catalog['authors'] ?? [];
            $kinds = is_array($authors) ? $authors : [];
        }
        $scores = [];
        $reasons = [];
        foreach ($kinds as $kind)
        {
            if (!is_array($kind) || !is_string($kind['title'] ?? null) || !is_array($kind['series_ids'] ?? null)) continue;
            $ids = array_unique(array_filter($kind['series_ids'], 'is_string'));
            $weight = count(array_intersect($ids, array_keys($ownedIds)));
            if ($weight === 0) continue;
            foreach ($ids as $id)
            {
                if (!isset($index[$id]) || isset($ownedIds[$id]) || in_array($id, $hidden, true) || $index[$id]['adult']) continue;
                $scores[$id] = ($scores[$id] ?? 0) + $weight;
                $reasons[$id][$kind['title']] = $weight;
            }
        }
        $ids = array_keys($scores);
        usort($ids, static fn (string $a, string $b): int => [$scores[$b], $index[$a]['title'], $a] <=> [$scores[$a], $index[$b]['title'], $b]);
        $result = [];
        foreach (array_slice($ids, 0, 56) as $id)
        {
            $covers = $catalog['covers'] ?? [];
            $cover = is_array($covers) ? ($covers[$id] ?? null) : null;
            $image = is_string($cover) && preg_match('/^[a-f0-9-]{36}$/D', $cover) === 1 ? 'images/manga/upcoming/' . $cover . '.jpg' : null;
            arsort($reasons[$id], SORT_NUMERIC);
            $categories = [];
            foreach (array_slice($reasons[$id], 0, 3, true) as $title => $points)
                $categories[] = ['title' => $title, 'points' => $points];
            $details = $catalog['details'] ?? [];
            $detail = is_array($details) ? ($details[$id] ?? []) : [];
            $detail = is_array($detail) ? $detail : [];
            $date = $detail['firstRelease'] ?? null;
            $parsed = is_string($date) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
            $result[] = ['volumeCount' => is_int($detail['volumeCount'] ?? null) ? $detail['volumeCount'] : null,
                'firstRelease' => $parsed !== false && $parsed->format('Y-m-d') === $date ? $parsed->format('d/m/Y') : null,
                'edition' => is_string($detail['edition'] ?? null) ? $detail['edition'] : null, 'score' => $scores[$id], 'categories' => $categories, 'id' => $id, 'title' => $index[$id]['title'], 'reason' => ($mode === 'authors' ? 'Auteurs communs : ' : 'Catégories communes : ') . implode(', ', array_keys($reasons[$id])),
                'imageUrl' => $image !== null && is_file(base_path('public/' . $image)) ? \Framework\Config\ApplicationConfig::baseUri() . $image : null];
        }
        return $result;
    }
    /** @return list<string> */
    public function hidden(): array
    {
        $owner = user();
        if ($owner === null) return [];
        return self::hiddenForOwner($owner->id, $this->hiddenPath);
    }

    /** @return list<string> */
    public static function hiddenForOwner(int $ownerId, ?string $customPath = null): array
    {
        $path = $customPath ?? base_path('storage/manga-recommendations-hidden-' . $ownerId . '.json');
        $contents = is_file($path) ? @file_get_contents($path) : false;
        $data = $contents === false ? null : json_decode($contents, true);
        return is_array($data) ? array_values(array_filter($data, 'is_string')) : [];
    }

    public function hide(string $id): \App\DTO\Common\ServiceResult
    {
        $owner = user();
        if ($owner === null) return \App\DTO\Common\ServiceResult::error('Connexion requise', status: 401);
        if (preg_match('/^[a-f0-9-]{36}$/D', $id) !== 1) return \App\DTO\Common\ServiceResult::error('Suggestion invalide', status: 422);
        $path = $this->hiddenPath ?? base_path('storage/manga-recommendations-hidden-' . $owner->id . '.json');
        $lock = fopen($path . '.lock', 'c');
        if ($lock === false) throw new \RuntimeException('Cannot lock recommendations');
        $temporary = false;
        try
        {
            if (!flock($lock, LOCK_EX)) throw new \RuntimeException('Cannot lock recommendations');
            $ids = array_values(array_unique([...$this->hidden(), $id]));
            $temporary = tempnam(dirname($path), '.build-');
            if ($temporary === false) throw new \RuntimeException('Cannot stage recommendations');
            $json = json_encode($ids, JSON_THROW_ON_ERROR);
            if (file_put_contents($temporary, $json) !== strlen($json) || !chmod($temporary, 0600) || !rename($temporary, $path))
                throw new \RuntimeException('Cannot save hidden recommendations');
        }
        finally
        {
            if ($temporary !== false && is_file($temporary)) unlink($temporary);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        return \App\DTO\Common\ServiceResult::success('Suggestion masquée');
    }
}
