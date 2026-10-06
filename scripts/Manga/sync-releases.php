<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';
require ROOT . '/scripts/Support/AtomicFile.php';
require ROOT . '/scripts/Manga/Support/MangacollecClient.php';
\Framework\Application\Bootstrap::loadEnvOnly();
date_default_timezone_set(\Framework\Config\ApplicationConfig::timezone());
$ownerId = isset($argv[1]) ? filter_var($argv[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null;
if ($ownerId === false || count($argv) > 2) throw new InvalidArgumentException('Usage: composer manga:sync [-- USER_ID]');
$lock = fopen(ROOT . '/storage/manga-releases.lock', 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) throw new RuntimeException('Release synchronization already running.');
try
{
    $db = new \Framework\Database\Database();
    if ($ownerId !== null)
    {
        $account = $db->prepare('SELECT id FROM users WHERE id = ?');
        $account->execute([$ownerId]);
        if ($account->fetchColumn() === false) throw new RuntimeException('Unknown collection owner.');
    }
    $statement = $db->prepare('SELECT user_id,slug,livre,editeur,numero FROM manga' . ($ownerId !== null ? ' WHERE user_id = :owner' : '') . ' ORDER BY user_id,slug,numero');
    $statement->execute($ownerId !== null ? ['owner' => $ownerId] : []);
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    $collections = [];
    foreach ($rows as $row)
    {
        $key = $row['user_id'] . '/' . $row['slug'];
        $collections[$key]['row'] = $row;
        $collections[$key]['owned'][] = (int) $row['numero'];
    }
    $settings = require ROOT . '/Config/settings/manga-releases.php';
    $path = ROOT . '/storage/manga-releases.json';
    $previous = is_file($path) ? json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : [];
    $catalog = ['updated_at' => date(DATE_ATOM), 'users' => []];
    if ($ownerId !== null)
    {
        $catalog['users'] = is_array($previous['users'] ?? null) ? $previous['users'] : [];
        unset($catalog['users'][(string) $ownerId]);
    }
    $client = new MangacollecClient();
    // The website downloads this public index once and searches it locally.
    $index = $client->get('/series');
    $normalize = static function (string $value): string
    {
        $value = str_replace(['×', '✕'], 'x', mb_strtolower($value));
        $value = transliterator_transliterate('Any-Latin; Latin-ASCII', $value);
        return preg_replace('/[^a-z0-9]+/', '', $value);
    };
    $seriesByTitle = [];
    $normalizedSeries = [];
    foreach ($index['series'] ?? [] as $series)
    {
        $normalizedTitle = $normalize($series['title']);
        $seriesByTitle[$normalizedTitle][] = $series;
        $normalizedSeries[] = ['title' => $normalizedTitle, 'series' => $series];
    }
    $errors = 0;
    foreach ($collections as $collection)
    {
        $row = $collection['row'];
        $slug = $row['slug'];
        $owner = (string) $row['user_id'];
        try
        {
            $editionId = $settings['editions'][$slug] ?? null;
            if ($editionId !== null)
            {
                $detail = $client->get('/editions/' . rawurlencode($editionId));
                $edition = null;
                foreach ($detail['editions'] ?? [] as $candidate)
                    if ($candidate['id'] === $editionId) $edition = $candidate;
                if ($edition === null) throw new RuntimeException('Configured edition missing');
            }
            else
            {
                $title = $settings['aliases'][$slug] ?? $row['livre'];
                $matches = $seriesByTitle[$normalize($title)] ?? [];
                if (count($matches) !== 1)
                {
                    $words = explode(' ', $title);
                    $hint = $normalize(implode(' ', array_slice($words, 0, 2)));
                    $near = array_column(array_filter($normalizedSeries, static fn ($candidate) => str_starts_with($candidate['title'], $hint)), 'series');
                    throw new RuntimeException('Series match needs configuration [' . implode(', ', array_column($near, 'title')) . ']');
                }
                $detail = $client->get('/series/' . rawurlencode($matches[0]['id']));
                $publishers = [];
                foreach ($detail['publishers'] ?? [] as $publisher) $publishers[$publisher['id']] = $publisher['title'];
                $editions = array_values(array_filter($detail['editions'] ?? [], static function ($edition) use ($row, $normalize, $publishers): bool
                {
                    $publisher = $publishers[$edition['publisher_id']] ?? '';
                    $expected = $normalize($row['editeur'] ?? '');
                    return $expected !== '' && ($normalize($publisher) === $expected ||
                        ($expected === 'panini' && $normalize($publisher) === 'paninimanga')) &&
                        ($edition['parent_edition_id'] ?? null) === null;
                }));
                if (count($editions) !== 1)
                {
                    $candidates = array_map(static fn ($e) => ($e['title'] ?? 'Standard') . ':' . $e['id'], $editions);
                    throw new RuntimeException('Edition match needs configuration [' . implode(', ', $candidates) . ']');
                }
                $edition = $editions[0];
                $editionId = $edition['id'];
                $detail = $client->get('/editions/' . rawurlencode($editionId));
            }
            $upcoming = [];
            foreach ($detail['volumes'] ?? [] as $volume)
            {
                $date = $volume['release_date'] ?? null;
                $number = $volume['number'] ?? null;
                if ($volume['edition_id'] !== $editionId || !is_string($date) || $date === '' ||
                    !is_int($number) || $number < 1 || in_array($number, $collection['owned'], true)) continue;
                $upcoming[] = ['id' => $volume['id'], 'number' => $number, 'release_date' => $date, 'isbn' => $volume['isbn'] ?? null];
                try
                { $client->cover($volume['id'], $volume['image_url'] ?? null); }
                catch (Throwable)
                { /* An unavailable cover must not hide an announced release. */ }
            }
            $catalog['users'][$owner][$slug] = ['edition_id' => $editionId, 'edition_title' => $edition['title'] ?? null,
                'checked_at' => date(DATE_ATOM), 'upcoming' => $upcoming];
            echo $slug . ': ' . count($upcoming) . ' unowned (' . ($edition['title'] ?? 'Standard') . ')' . PHP_EOL;
        }
        catch (Throwable $error)
        {
            $errors++;
            if (isset($previous['users'][$owner][$slug])) $catalog['users'][$owner][$slug] = $previous['users'][$owner][$slug];
            echo $slug . ': ' . $error->getMessage() . "\n";
        }
    }
    AtomicFile::writeIfChanged($path, json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n", 0600);
    echo 'Synchronization complete; unresolved/failed series: ' . $errors . "\n";
    if ($errors > 0) exit(1);
}
finally
{
    flock($lock, LOCK_UN);
    fclose($lock);
}
