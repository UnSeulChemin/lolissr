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
        static $transliterator = null;
        $transliterator ??= \Transliterator::create('Any-Latin; Latin-ASCII');
        $title = $transliterator === null ? false : $transliterator->transliterate(mb_strtolower(str_replace('×', 'x', $title)));
        $normalized = preg_replace('/[^a-z0-9]+/', '', $title === false ? '' : $title) ?? '';
        return $normalized;
    }

    /** @return list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}> */
    public function all(string $mode = 'categories'): array
    {
        if (user() === null) return [];
        $path = $this->catalogPath ?? base_path('storage/manga-recommendations.json');
        $contents = is_file($path) ? @file_get_contents($path) : false;
        $catalog = $contents === false ? null : json_decode($contents, true);
        if (!is_array($catalog)) return [];
        $rows = $this->repository->releaseCollection();
        $titles = array_column($rows, 'livre');
        return self::fromCatalog($catalog, $titles, $mode, $this->hidden(), self::confirmedSeriesIds($catalog, $rows, user()->id));
    }

    /**
     * @param array<mixed> $catalog
     * @param list<string> $titles
     * @param list<string> $hidden
     * @param list<string> $confirmedIds
     * @return list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}>
     */
    public static function fromCatalog(array $catalog, array $titles, string $mode = 'categories', array $hidden = [], array $confirmedIds = []): array
    {
        if ($titles === [] && $confirmedIds === []) return [];
        $series = $catalog['series'] ?? null;
        $kinds = $catalog['kinds'] ?? null;
        if (!is_array($series) || !is_array($kinds)) return [];
        $owned = [];
        foreach (array_unique($titles) as $title) $owned[self::normalize($title)] = true;
        $index = [];
        $ownedIds = [];
        $titlesIndex = [];
        $confirmed = array_fill_keys($confirmedIds, true);
        foreach ($series as $entry)
        {
            if (!is_array($entry) || !is_string($entry['id'] ?? null) || !is_string($entry['title'] ?? null)) continue;
            if (preg_match('/^[a-f0-9-]{36}$/D', $entry['id']) !== 1) continue;
            $index[$entry['id']] = ['title' => $entry['title'], 'adult' => ($entry['adult_content'] ?? false) !== false];
            $normalizedTitle = is_string($entry['normalized_title'] ?? null) ? $entry['normalized_title'] : self::normalize($entry['title']);
            $titlesIndex[$normalizedTitle][] = $entry['id'];
            if (isset($confirmed[$entry['id']])) $ownedIds[$entry['id']] = true;
        }
        $excludeIds = $ownedIds;
        foreach (array_keys($owned) as $title)
        {
            $matches = $titlesIndex[$title] ?? [];
            foreach ($matches as $id) $excludeIds[$id] = true;
            if (count($matches) === 1) $ownedIds[$matches[0]] = true;
        }
        if ($ownedIds === []) return [];
        $hiddenSet = array_fill_keys($hidden, true);
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
            $ids = array_fill_keys(array_filter($kind['series_ids'], 'is_string'), true);
            $weight = count(array_intersect_key($ids, $ownedIds));
            if ($weight === 0) continue;
            foreach ($ids as $id => $_)
            {
                if (!isset($index[$id]) || isset($excludeIds[$id]) || isset($hiddenSet[$id]) || $index[$id]['adult']) continue;
                $scores[$id] = ($scores[$id] ?? 0) + $weight;
                $reasons[$id][$kind['title']] = $weight;
            }
        }
        $ids = array_keys($scores);
        usort($ids, static fn (string $a, string $b): int => [$scores[$b], $index[$a]['title'], $a] <=> [$scores[$a], $index[$b]['title'], $b]);
        $result = [];
        foreach (array_slice($ids, 0, 40) as $id)
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
    /**
     * @param array<mixed> $catalog
     * @param list<array{slug: string, livre: string, numero?: int}> $rows
     * @return list<string>
     */
    public static function confirmedSeriesIds(array $catalog, array $rows, int $ownerId): array
    {
        /** @var array{editions: array<string, string>} $settings */
        $settings = require base_path('Config/settings/manga-releases.php');
        $path = base_path('storage/manga-releases.json');
        $contents = is_file($path) ? @file_get_contents($path) : false;
        $releases = $contents === false ? null : json_decode($contents, true);
        $owners = is_array($releases) ? ($releases['users'] ?? []) : [];
        $series = is_array($owners) ? ($owners[(string) $ownerId] ?? []) : [];
        $map = $catalog['edition_series'] ?? [];
        if (!is_array($map)) return [];
        $ids = [];
        foreach ($rows as $row)
        {
            $saved = is_array($series) ? ($series[$row['slug']] ?? []) : [];
            $edition = $settings['editions'][$row['slug']] ?? (is_array($saved) ? ($saved['edition_id'] ?? null) : null);
            $id = is_string($edition) ? ($map[$edition] ?? null) : null;
            if (is_string($id) && preg_match('/^[a-f0-9-]{36}$/D', $id) === 1) $ids[$id] = true;
        }
        return array_keys($ids);
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
