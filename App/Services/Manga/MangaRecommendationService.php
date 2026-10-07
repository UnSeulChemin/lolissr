<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Repositories\Manga\MangaRepository;

/**
 * @phpstan-type CatalogIndex array{index: array<string, array{title: string, adult: bool}>, ownedIds: array<string, true>, excludeIds: array<string, true>}
 * @phpstan-type Recommendation array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}
 */
final class MangaRecommendationService
{
    private ?string $catalogFingerprint = null;
    /** @var array<mixed>|null */
    private ?array $catalogData = null;
    /** @var array<string, string|false> */
    private array $fileFingerprints = [];

    private function collectionRevision(): string
    {
        return $this->repository->collectionRevision()
            ?? hash('sha256', json_encode($this->repository->releaseCollection(), JSON_THROW_ON_ERROR));
    }

    private function fingerprint(string $path): string|false
    {
        if ($this->catalogPath !== null) return is_file($path) ? @hash_file('sha256', $path) : false;
        if ($path === base_path('storage/manga-recommendations.json'))
            return $this->fileFingerprints[$path] ??= \App\Support\Manga\MangaCatalogRevision::fingerprint($path);
        return $this->fileFingerprints[$path] ??= is_file($path) ? @hash_file('sha256', $path) : false;
    }

    /** @return array<mixed>|null */
    private function catalog(): ?array
    {
        $path = $this->catalogPath ?? base_path('storage/manga-recommendations.json');
        $fingerprint = $this->fingerprint($path);
        if ($fingerprint === false) return null;
        if ($fingerprint === $this->catalogFingerprint) return $this->catalogData;
        $contents = @file_get_contents($path);
        $decoded = $contents === false ? null : json_decode($contents, true);
        $this->catalogFingerprint = $fingerprint;
        return $this->catalogData = is_array($decoded) ? $decoded : null;
    }
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
        $hidden = $this->hidden();
        $ownerId = user()->id;
        $compute = function () use ($mode, $hidden, $ownerId): array
        {
            $catalog = $this->catalog();
            if (!is_array($catalog)) return [];
            $rows = $this->repository->releaseCollection();
            return self::fromCatalog($catalog, array_column($rows, 'livre'), $mode, $hidden, self::confirmedSeriesIds($catalog, $rows, $ownerId));
        };
        if ($this->catalogPath !== null || !is_file($path)) return $compute();
        $fingerprint = [$ownerId, $mode, \Framework\Config\ApplicationConfig::baseUri(), $this->fingerprint($path), $this->collectionRevision(), $hidden];
        foreach (['storage/manga-releases.json', 'Config/settings/manga-releases.php'] as $relative)
        {
            $file = base_path($relative);
            $fingerprint[] = $this->fingerprint($file);
        }
        $revision = hash('sha256', json_encode($fingerprint, JSON_THROW_ON_ERROR));
        $key = 'manga.recommendations.v4.' . $ownerId . '.' . $mode;
        /** @var list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}> $items */
        $items = \Framework\Cache\Cache::rememberRevision($key, $revision, 3600, $compute);
        return $items;
    }

    /**
     * @param array<mixed> $catalog
     * @param list<string> $titles
     * @param list<string> $hidden
     * @param list<string> $confirmedIds
     * @param list<string>|null $onlyIds
     * @return list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}>
     */
    public static function fromCatalog(array $catalog, array $titles, string $mode = 'categories', array $hidden = [], array $confirmedIds = [], ?array $onlyIds = null): array
    {
        $prepared = self::prepareCatalog($catalog, $titles, $confirmedIds);
        return $prepared === null ? [] : self::fromPreparedCatalog($catalog, $prepared, $mode, $hidden, $onlyIds);
    }

    /**
     * @param array<mixed> $catalog
     * @param list<string> $titles
     * @param list<string> $confirmedIds
     * @return CatalogIndex|null
     */
    private static function prepareCatalog(array $catalog, array $titles, array $confirmedIds): ?array
    {
        if ($titles === [] && $confirmedIds === []) return null;
        $series = $catalog['series'] ?? null;
        $kinds = $catalog['kinds'] ?? null;
        if (!is_array($series) || !is_array($kinds)) return null;
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
        if ($ownedIds === []) return null;
        return ['index' => $index, 'ownedIds' => $ownedIds, 'excludeIds' => $excludeIds];
    }

    /**
     * @param array<mixed> $catalog
     * @param CatalogIndex $prepared
     * @param list<string> $hidden
     * @param list<string>|null $onlyIds
     * @return list<Recommendation>
     */
    private static function fromPreparedCatalog(array $catalog, array $prepared, string $mode, array $hidden, ?array $onlyIds = null): array
    {
        ['index' => $index, 'ownedIds' => $ownedIds, 'excludeIds' => $excludeIds] = $prepared;
        $kinds = $catalog['kinds'] ?? [];
        $kinds = is_array($kinds) ? $kinds : [];
        $hiddenSet = array_fill_keys($hidden, true);
        $selected = $onlyIds === null ? null : array_fill_keys($onlyIds, true);
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
                if (!isset($index[$id]) || isset($excludeIds[$id]) || isset($hiddenSet[$id]) || $index[$id]['adult'] || ($selected !== null && !isset($selected[$id]))) continue;
                $scores[$id] = ($scores[$id] ?? 0) + $weight;
                $reasons[$id][$kind['title']] = $weight;
            }
        }
        $ids = array_keys($scores);
        usort($ids, static fn (string $a, string $b): int => [$scores[$b], $index[$a]['title'], $a] <=> [$scores[$a], $index[$b]['title'], $b]);
        $result = [];
        foreach ($onlyIds === null ? array_slice($ids, 0, 40) : $ids as $id)
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
        if (!is_array($entries) || $entries === []) return [];
        $ownerId = user()->id;
        $compute = function () use ($entries, $ownerId): array
        {
            $catalog = $this->catalog();
            if (is_array($catalog))
            {
                $rows = $this->repository->releaseCollection();
                $confirmed = self::confirmedSeriesIds($catalog, $rows, $ownerId);
                $current = [];
                foreach (['categories', 'authors'] as $mode)
                {
                    $ids = [];
                    foreach ($entries as $entry)
                        if ((str_starts_with($entry['reason'], 'Auteurs communs : ') ? 'authors' : 'categories') === $mode) $ids[] = $entry['id'];
                    if ($ids === []) continue;
                    foreach (self::fromCatalog($catalog, array_column($rows, 'livre'), $mode, [], $confirmed, $ids) as $suggestion)
                        $current[$suggestion['id']] = $suggestion;
                }
                $titles = [];
                foreach (is_array($catalog['series'] ?? null) ? $catalog['series'] : [] as $series)
                    if (is_array($series) && is_string($series['id'] ?? null) && is_string($series['title'] ?? null)) $titles[$series['id']] = $series['title'];
                $details = is_array($catalog['details'] ?? null) ? $catalog['details'] : [];
                $covers = is_array($catalog['covers'] ?? null) ? $catalog['covers'] : [];
                foreach ($entries as &$entry)
                {
                    if (isset($titles[$entry['id']]))
                    {
                        $entry['title'] = $titles[$entry['id']];
                        $fresh = $current[$entry['id']] ?? null;
                        $entry['score'] = $fresh['score'] ?? 0;
                        $entry['categories'] = $fresh['categories'] ?? [];
                        $entry['reason'] = $fresh['reason'] ?? (str_starts_with($entry['reason'], 'Auteurs communs : ') ? 'Auteurs communs : ' : 'Catégories communes : ');
                    }
                    $detail = $details[$entry['id']] ?? null;
                    if (is_array($detail))
                    {
                        if (is_int($detail['volumeCount'] ?? null)) $entry['volumeCount'] = $detail['volumeCount'];
                        if (is_string($detail['edition'] ?? null)) $entry['edition'] = $detail['edition'];
                        $date = $detail['firstRelease'] ?? null;
                        $parsed = is_string($date) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
                        $entry['firstRelease'] = $parsed !== false && $parsed->format('Y-m-d') === $date ? $parsed->format('d/m/Y') : null;
                    }
                    $cover = $covers[$entry['id']] ?? null;
                    if (is_string($cover) && preg_match('/^[a-f0-9-]{36}$/D', $cover) === 1 && is_file(base_path('public/images/manga/upcoming/' . $cover . '.jpg')))
                        $entry['imageUrl'] = \Framework\Config\ApplicationConfig::baseUri() . 'images/manga/upcoming/' . $cover . '.jpg';
                }
                unset($entry);
            }
            return array_values($entries);
        };
        if ($this->catalogPath !== null || $this->favoritesPath !== null) return $compute();
        $revision = hash('sha256', json_encode([$entries, $this->collectionRevision(), \Framework\Config\ApplicationConfig::baseUri(),
            $this->fingerprint(base_path('storage/manga-recommendations.json')),
            $this->fingerprint(base_path('storage/manga-releases.json')),
            $this->fingerprint(base_path('Config/settings/manga-releases.php'))], JSON_THROW_ON_ERROR));
        $key = 'manga.favorites.v3.' . user()->id;
        /** @var list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}> $items */
        $items = \Framework\Cache\Cache::rememberRevision($key, $revision, 3600, $compute);
        return $items;
    }

    /** @return list<string> */
    public static function favoriteIdsForOwner(int $ownerId, ?string $path = null): array
    {
        if ($ownerId < 1) return [];
        $path ??= base_path('storage/manga-recommendations-favorites-' . $ownerId . '.json');
        $contents = is_file($path) ? @file_get_contents($path) : false;
        $entries = $contents === false ? null : json_decode($contents, true);
        $ids = [];
        foreach (is_array($entries) ? $entries : [] as $entry)
            if (is_array($entry) && is_string($entry['id'] ?? null) && preg_match('/^[a-f0-9-]{36}$/D', $entry['id']) === 1)
                $ids[$entry['id']] = true;
        return array_keys($ids);
    }

    public function setFavorite(string $id, bool $save): \App\DTO\Common\ServiceResult
    {
        $owner = user();
        if ($owner === null) return \App\DTO\Common\ServiceResult::error('Connexion requise', status: 401);
        if (preg_match('/^[a-f0-9-]{36}$/D', $id) !== 1) return \App\DTO\Common\ServiceResult::error('Suggestion invalide', status: 422);
        $entry = null;
        if ($save)
        {
            foreach (['favorites', 'categories', 'authors'] as $mode)
            {
                $candidates = $mode === 'favorites' ? $this->favorites() : $this->all($mode);
                foreach ($candidates as $candidate)
                    if ($candidate['id'] === $id)
                    { $entry = $candidate; break; }
                if ($entry !== null) break;
            }
            if ($entry === null) return \App\DTO\Common\ServiceResult::error('Suggestion introuvable', status: 404);
        }
        $path = $this->favoritesPath ?? base_path('storage/manga-recommendations-favorites-' . $owner->id . '.json');
        self::updateJson($path, static function (array $saved) use ($save, $id, $entry): array
        {
            $entries = [];
            foreach ($saved as $favorite)
                if (is_array($favorite) && is_string($favorite['id'] ?? null)) $entries[$favorite['id']] = $favorite;
            if ($save) $entries[$id] = $entry;
            else unset($entries[$id]);
            return $entries;
        }, JSON_UNESCAPED_UNICODE);
        return \App\DTO\Common\ServiceResult::success($save ? 'Série ajoutée aux favoris' : 'Série retirée des favoris');
    }

    /** @return array{categories: list<array{title: string, url: string, symbol: string, description: string}>, authors: list<array{title: string, url: string, symbol: string, description: string}>} */
    public function searchFilters(string $query): array
    {
        $result = ['categories' => [], 'authors' => []];
        $query = self::normalize($query);
        $owner = user();
        if ($query === '' || $owner === null) return $result;
        $hidden = $this->hidden();
        $ownerId = $owner->id;
        $compute = function () use ($hidden, $ownerId): array
        {
            $filters = ['categories' => [], 'authors' => []];
            $catalog = $this->catalog();
            if ($catalog === null) return $filters;
            $rows = $this->repository->releaseCollection();
            $prepared = self::prepareCatalog($catalog, array_column($rows, 'livre'), self::confirmedSeriesIds($catalog, $rows, $ownerId));
            if ($prepared === null) return $filters;
            foreach (['categories', 'authors'] as $mode)
            {
                $titles = [];
                foreach (self::fromPreparedCatalog($catalog, $prepared, $mode, $hidden) as $entry)
                    foreach ($entry['categories'] as $badge) $titles[$badge['title']] = true;
                $titles = array_keys($titles);
                sort($titles, SORT_NATURAL | SORT_FLAG_CASE);
                foreach ($titles as $title)
                    $filters[$mode][] = ['title' => $title, 'normalized' => self::normalize($title)];
            }
            return $filters;
        };
        $path = $this->catalogPath ?? base_path('storage/manga-recommendations.json');
        if ($this->catalogPath !== null || !is_file($path))
        {
            $filters = $compute();
        }
        else
        {
            $revision = hash('sha256', json_encode([$ownerId, $this->collectionRevision(), $hidden, \Framework\Config\ApplicationConfig::baseUri(),
                $this->fingerprint($path), $this->fingerprint(base_path('storage/manga-releases.json')),
                $this->fingerprint(base_path('Config/settings/manga-releases.php'))], JSON_THROW_ON_ERROR));
            /** @var array{categories: list<array{title: string, normalized: string}>, authors: list<array{title: string, normalized: string}>} $filters */
            $filters = \Framework\Cache\Cache::rememberRevision('manga.recommendation-filters.v2.' . $ownerId, $revision, 3600, $compute);
        }
        foreach (['categories', 'authors'] as $mode)
        {
            foreach ($filters[$mode] as $filter)
            {
                if (!str_contains($filter['normalized'], $query)) continue;
                $title = $filter['title'];
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
        $catalog = $this->catalog() ?? [];
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
        self::updateJson($path, static function (array $saved) use ($hide, $id): array
        {
            $ids = array_values(array_filter($saved, 'is_string'));
            return $hide ? array_values(array_unique([...$ids, $id])) : array_values(array_diff($ids, [$id]));
        });
        return \App\DTO\Common\ServiceResult::success($hide ? 'Suggestion masquée' : 'Suggestion rétablie');
    }

    /** @param callable(array<mixed>): array<mixed> $update */
    private static function updateJson(string $path, callable $update, int $flags = 0): void
    {
        // Keep the lock file stable across atomic replacements of the JSON file.
        $lock = fopen($path . '.lock', 'c');
        if ($lock === false) throw new \RuntimeException('Cannot lock recommendation data.');
        $temporary = false;
        try
        {
            if (!flock($lock, LOCK_EX)) throw new \RuntimeException('Cannot lock recommendation data.');
            $contents = is_file($path) ? @file_get_contents($path) : false;
            $saved = $contents === false ? null : json_decode($contents, true);
            // Preserve the existing recovery policy: missing, unreadable or invalid JSON starts empty.
            $data = $update(is_array($saved) ? $saved : []);
            $temporary = tempnam(dirname($path), '.build-');
            if ($temporary === false) throw new \RuntimeException('Cannot stage recommendation data.');
            $json = json_encode($data, JSON_THROW_ON_ERROR | $flags);
            // If replacement fails (including on Windows), keep the previous file intact.
            if (file_put_contents($temporary, $json) !== strlen($json) || !chmod($temporary, 0600) || !rename($temporary, $path))
                throw new \RuntimeException('Cannot save recommendation data.');
        }
        finally
        {
            if ($temporary !== false && is_file($temporary)) unlink($temporary);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
