<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SMOKE TEST GLOBAL
|--------------------------------------------------------------------------
*/

addGetTest($tests, [
    'category' => 'Smoke',
    'label' => 'Accueil',
    'path' => '/',
    'expected_status' => 200,
    'must_contain' => ['<body'],
]);

addGetTest($tests, [
    'category' => 'Smoke',
    'label' => 'Dashboard manga',
    'path' => '/manga',
    'expected_status' => 200,
    'must_contain' => ['Manga'],
]);

addGetTest($tests, [
    'category' => 'Smoke',
    'label' => 'Collection',
    'path' => '/manga/collection',
    'expected_status' => 200,
    'must_contain' => ['Collection'],
]);

addGetTest($tests, [
    'category' => 'Smoke',
    'label' => 'Recherche',
    'path' => '/manga/recherche',
    'expected_status' => 200,
]);

addGetTest($tests, [
    'category' => 'Smoke',
    'label' => 'Ajouter',
    'path' => '/manga/ajouter',
    'expected_status' => 200,
    'must_contain' => ['<form', 'Livre', 'slug', 'numero'],
]);

addGetTest($tests, [
    'category' => 'Smoke',
    'label' => 'Page lien',
    'path' => '/manga/lien',
    'expected_status' => 200,
]);

addGetTest($tests, [
    'category' => 'Smoke',
    'label' => 'Série existante',
    'path' => '/manga/serie/' . $realSlug,
    'expected_status' => 200,
]);

addGetTest($tests, [
    'category' => 'Smoke',
    'label' => 'Tome existant',
    'path' => '/manga/' . $realSlug . '/' . $realNumero,
    'expected_status' => 200,
]);

addGetTest($tests, [
    'category' => 'Smoke',
    'label' => 'Page modifier',
    'path' => '/manga/update/' . $realSlug . '/' . $realNumero,
    'expected_status' => 200,
    'must_contain' => ['<form'],
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Smoke HTML',
    'label' => 'Detail a un title',
    'url' => $base . '/manga/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $response = requestUrl($base . '/manga/' . $realSlug . '/' . $realNumero);
        $title = extractTitle($response['body']);

        return [
            'ok' => $response['status'] === 200 && !empty($title),
            'message' => $response['status'] === 200
                ? ($title ?: 'title absent')
                : 'page détail inaccessible',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Smoke HTML',
    'label' => 'Detail contient une image',
    'url' => $base . '/manga/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $response = requestUrl($base . '/manga/' . $realSlug . '/' . $realNumero);

        return [
            'ok' => $response['status'] === 200 && preg_match('/<img\b/i', $response['body']) === 1,
            'message' => $response['status'] === 200 ? 'img trouvée' : 'page détail inaccessible',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Smoke HTML',
    'label' => 'Collection contient plusieurs liens',
    'url' => $base . '/manga/collection',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl($base . '/manga/collection');
        $linkCount = countOccurrences($response['body'], '<a');

        return [
            'ok' => $response['status'] === 200 && $linkCount >= 3,
            'message' => $response['status'] === 200
                ? $linkCount . ' lien(s)'
                : 'collection inaccessible',
        ];
    },
]);