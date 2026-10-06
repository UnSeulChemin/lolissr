<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Repositories\Manga\MangaRepository;

final class MangaRecommendationService
{
    public function __construct(private readonly MangaRepository $repository, private readonly ?string $catalogPath = null, private readonly ?string $hiddenPath = null, private readonly ?string $favoritesPath = null)
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
            foreach ($reasons[$id] as $title => $points)
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

    /** @return list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}> */
    public function favorites(): array
    {
        if (user() === null) return [];
        $path = $this->favoritesPath ?? base_path('storage/manga-recommendations-favorites-' . user()->id . '.json');
        $contents = is_file($path) ? @file_get_contents($path) : false;
        /** @var array<string, array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}>|null $entries */
        $entries = $contents === false ? null : json_decode($contents, true);
        return is_array($entries) ? array_values($entries) : [];
    }

    public function setFavorite(string $id, bool $save): \App\DTO\Common\ServiceResult
    {
        $owner = user();
        if ($owner === null) return \App\DTO\Common\ServiceResult::error('Connexion requise', status: 401);
        if (preg_match('/^[a-f0-9-]{36}$/D', $id) !== 1) return \App\DTO\Common\ServiceResult::error('Suggestion invalide', status: 422);
        $entry = null;
        if ($save)
        {
            foreach ([...$this->favorites(), ...$this->all(), ...$this->all('authors')] as $candidate)
                if ($candidate['id'] === $id)
                { $entry = $candidate; break; }
            if ($entry === null) return \App\DTO\Common\ServiceResult::error('Suggestion introuvable', status: 404);
        }
        $path = $this->favoritesPath ?? base_path('storage/manga-recommendations-favorites-' . $owner->id . '.json');
        $lock = fopen($path . '.lock', 'c');
        if ($lock === false) throw new \RuntimeException('Cannot lock favorites');
        $temporary = false;
        try
        {
            if (!flock($lock, LOCK_EX)) throw new \RuntimeException('Cannot lock favorites');
            $entries = array_column($this->favorites(), null, 'id');
            if ($save) $entries[$id] = $entry;
            else unset($entries[$id]);
            $temporary = tempnam(dirname($path), '.build-');
            if ($temporary === false) throw new \RuntimeException('Cannot stage favorites');
            $json = json_encode($entries, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            if (file_put_contents($temporary, $json) !== strlen($json) || !chmod($temporary, 0600) || !rename($temporary, $path))
                throw new \RuntimeException('Cannot save favorites');
        }
        finally
        {
            if ($temporary !== false && is_file($temporary)) unlink($temporary);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        return \App\DTO\Common\ServiceResult::success($save ? 'Série ajoutée aux favoris' : 'Série retirée des favoris');
    }

    /** @return array{categories: list<array{title: string, url: string, symbol: string, description: string}>, authors: list<array{title: string, url: string, symbol: string, description: string}>} */
    public function searchFilters(string $query): array
    {
        $result = ['categories' => [], 'authors' => []];
        $query = self::normalize($query);
        if ($query === '' || user() === null) return $result;
        foreach (['categories', 'authors'] as $mode)
        {
            $titles = [];
            foreach ($this->all($mode) as $entry)
                foreach ($entry['categories'] as $badge) $titles[$badge['title']] = true;
            $titles = array_keys($titles);
            sort($titles, SORT_NATURAL | SORT_FLAG_CASE);
            foreach ($titles as $title)
            {
                if (!str_contains(self::normalize($title), $query)) continue;
                $result[$mode][] = ['title' => $title,
                    'url' => $mode === 'authors' ? 'manga/series/recommandations-auteurs/auteur/' . \Framework\Support\Strings::asciiSlug($title) : 'manga/series/recommandations/categorie/' . rawurlencode(mb_strtolower($title)),
                    'symbol' => $mode === 'authors' ? '✍️' : '✨', 'description' => $mode === 'authors' ? 'Recommandations de cet auteur' : 'Recommandations de cette catégorie'];
                if (count($result[$mode]) === 5) break;
            }
        }
        return $result;
    }

    /** @return list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}> */
    public function hiddenSuggestions(): array
    {
        $ids = $this->hidden();
        if ($ids === []) return [];
        $path = $this->catalogPath ?? base_path('storage/manga-recommendations.json');
        $contents = is_file($path) ? @file_get_contents($path) : false;
        $catalog = $contents === false ? [] : json_decode($contents, true);
        $catalog = is_array($catalog) ? $catalog : [];
        $series = is_array($catalog['series'] ?? null) ? $catalog['series'] : [];
        $titles = [];
        foreach ($series as $entry)
            if (is_array($entry) && is_string($entry['id'] ?? null) && is_string($entry['title'] ?? null)) $titles[$entry['id']] = $entry['title'];
        $covers = is_array($catalog['covers'] ?? null) ? $catalog['covers'] : [];
        $result = [];
        foreach ($ids as $id)
        {
            if (preg_match('/^[a-f0-9-]{36}$/D', $id) !== 1) continue;
            $cover = $covers[$id] ?? null;
            $image = is_string($cover) && preg_match('/^[a-f0-9-]{36}$/D', $cover) === 1 ? 'images/manga/upcoming/' . $cover . '.jpg' : null;
            $result[] = ['id' => $id, 'title' => $titles[$id] ?? 'Série indisponible dans le catalogue', 'reason' => 'Suggestion masquée',
                'imageUrl' => $image !== null && is_file(base_path('public/' . $image)) ? \Framework\Config\ApplicationConfig::baseUri() . $image : null,
                'score' => 0, 'volumeCount' => null, 'firstRelease' => null, 'edition' => null, 'categories' => []];
        }
        return $result;
    }

    public function hide(string $id, bool $hide = true): \App\DTO\Common\ServiceResult
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
            $ids = $hide ? array_values(array_unique([...$this->hidden(), $id])) : array_values(array_diff($this->hidden(), [$id]));
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
        return \App\DTO\Common\ServiceResult::success($hide ? 'Suggestion masquée' : 'Suggestion rétablie');
    }
}
