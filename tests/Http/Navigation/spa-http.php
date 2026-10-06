<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/Support/HttpClient.php';

http_login();
$jsonHeaders = ['Accept: application/json'];
$fragmentHeaders = [...$jsonHeaders, 'X-Page-Format: fragment'];
foreach (['/', '/profil', '/profil/succes', '/manga', '/manga/series/recommandations', '/manga/series/recommandations-auteurs', '/chinois', '/figurine'] as $path)
{
    $fullResponse = http_get(http_base() . $path, $jsonHeaders);
    $fragmentResponse = http_get(http_base() . $path, $fragmentHeaders);
    $full = json_decode($fullResponse['body'], true, 512, JSON_THROW_ON_ERROR);
    $fragment = json_decode($fragmentResponse['body'], true, 512, JSON_THROW_ON_ERROR);
    if ($fullResponse['status'] !== 200 || $fragmentResponse['status'] !== 200
        || ($full['page']['format'] ?? '') !== 'document'
        || ($fragment['page']['format'] ?? '') !== 'fragment'
        || ($fragment['page']['lang'] ?? '') !== 'fr'
        || ! array_key_exists('bodyData', $fragment['page']))
    {
        throw new RuntimeException('Invalid navigation protocol: ' . $path);
    }
    if (preg_match('~<main class="app-content">(.*?)</main>~s', $full['page']['html'], $match) !== 1
        || trim($match[1]) !== trim($fragment['page']['html'])
        || $full['page']['title'] !== $fragment['page']['title']
        || $full['page']['stylesheets'] !== $fragment['page']['stylesheets'])
    {
        throw new RuntimeException('SPA fragment differs from full-page content: ' . $path);
    }
    echo 'PASS: ' . $path . ' — JSON ' . strlen($fullResponse['body']) . ' -> ' . strlen($fragmentResponse['body']) . " bytes\n";
}

$endpoints = [
    'mangas' => '/manga/ajax/recherche/',
    'artbooks' => '/manga/ajax/recherche/artbooks/',
    'chinois' => '/chinois/ajax/recherche/',
    'figurines' => '/figurine/ajax/recherche/',
    'nendoroids' => '/nendoroid/ajax/recherche/',
    'peluches' => '/peluche/ajax/recherche/'
];
foreach (['a', 'HSK', '测试'] as $query)
{
    $response = http_get(http_base() . '/recherche?q=' . rawurlencode($query), $jsonHeaders);
    $grouped = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);
    if ($response['status'] !== 200 || ($grouped['success'] ?? false) !== true)
    {
        throw new RuntimeException('Global search failed.');
    }
    foreach ($endpoints as $category => $endpoint)
    {
        $legacy = http_get(http_base() . $endpoint . rawurlencode($query), $jsonHeaders);
        $data = json_decode($legacy['body'], true, 512, JSON_THROW_ON_ERROR);
        if ($legacy['status'] !== 200 || ($grouped['data'][$category] ?? null) !== array_slice($data['data']['results'] ?? [], 0, 5))
        {
            throw new RuntimeException('Global search changed results: ' . $category);
        }
    }
}
echo "PASS: global search returns the same first five results for all six categories (three queries).\n";
$invalid = http_get(http_base() . '/recherche?q%5B%5D=test', $jsonHeaders);
if ($invalid['status'] !== 422) throw new RuntimeException('Search must reject array input.');
echo "PASS: invalid search input rejected.\n";

foreach (['vocabulaire', 'grammaire'] as $type)
{
    foreach ([0, 50, PHP_INT_MAX] as $offset)
    {
        $response = http_get(http_base() . "/chinois/flashcards/$type/batch/$offset", $jsonHeaders);
        $payload = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);
        $page = $payload['data'] ?? [];
        if ($response['status'] !== 200 || ($payload['success'] ?? false) !== true
            || !is_array($page['cards'] ?? null) || count($page['cards']) > 50
            || !is_int($page['total'] ?? null) || !is_int($page['offset'] ?? null))
        {
            throw new RuntimeException("Invalid flashcard batch: $type/$offset");
        }
    }
}
echo "PASS: both flashcard batch endpoints accept zero and extreme offsets with bounded payloads.\n";

foreach (['vocabulaire', 'grammaire'] as $type)
{
    foreach (['next', 'previous'] as $direction)
    {
        foreach ([0, PHP_INT_MAX] as $cursor)
        {
            $response = http_get(http_base() . "/chinois/flashcards/$type/cursor/$direction/$cursor", $jsonHeaders);
            $payload = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);
            $page = $payload['data'] ?? [];
            if ($response['status'] !== 200 || ($payload['success'] ?? false) !== true
                || !is_array($page['cards'] ?? null) || count($page['cards']) > 50
                || !is_int($page['total'] ?? null) || !is_int($page['offset'] ?? null)
                || $page['offset'] + count($page['cards']) > $page['total'])
                throw new RuntimeException("Invalid flashcard cursor: $type/$direction/$cursor");
        }
    }
    if (http_get(http_base() . "/chinois/flashcards/$type/cursor/invalid/0", $jsonHeaders)['status'] !== 404)
        throw new RuntimeException('Invalid cursor direction accepted');
}
foreach (['/chinois/grammaire/hsk1?section=missing-fixture', '/manga/series/missing-fixture/page/2'] as $path)
{
    if (http_get(http_base() . $path, $jsonHeaders)['status'] !== 404)
        throw new RuntimeException('Missing section/series page accepted');
}
echo "PASS: cursor endpoints, wrap directions and missing section/series pages.\n";
