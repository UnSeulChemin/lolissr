<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS PAGINATION
|--------------------------------------------------------------------------
*/

addGetTest($tests, [
    'category' => 'Pagination',
    'label' => 'Collection page 2',
    'path' => '/manga/collection/page/2',
    'expected_status' => 200,
]);

addGetTest($tests, [
    'category' => 'Pagination',
    'label' => 'Collection page 0 refusée',
    'path' => '/manga/collection/page/0',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'Pagination',
    'label' => 'Collection page négative refusée',
    'path' => '/manga/collection/page/-1',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'Pagination',
    'label' => 'Collection page très haute refusée',
    'path' => '/manga/collection/page/9999',
    'expected_status' => 404,
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection page 1 contient du contenu',
    'url' => $base . '/manga/collection',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl($base . '/manga/collection');
        $body = $response['body'];

        return [
            'ok' => $response['status'] === 200
                && (
                    stripos($body, 'collection-card') !== false
                    || stripos($body, '<article') !== false
                    || stripos($body, '<a') !== false
                ),
            'message' => $response['status'] === 200
                ? 'contenu détecté'
                : 'page inaccessible',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection page 2 contient du contenu',
    'url' => $base . '/manga/collection/page/2',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl($base . '/manga/collection/page/2');
        $body = $response['body'];

        return [
            'ok' => $response['status'] === 200
                && (
                    stripos($body, 'collection-card') !== false
                    || stripos($body, '<article') !== false
                    || stripos($body, '<a') !== false
                ),
            'message' => $response['status'] === 200
                ? 'contenu détecté'
                : 'page inaccessible',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection page 2 différente de la page 1',
    'url' => $base . '/manga/collection/page/2',
    'callback' => static function () use ($base): array
    {
        $page1 = requestUrl($base . '/manga/collection');
        $page2 = requestUrl($base . '/manga/collection/page/2');

        if ($page1['status'] !== 200 || $page2['status'] !== 200)
        {
            return [
                'ok' => false,
                'message' => 'une des pages pagination est inaccessible',
            ];
        }

        $different = md5($page1['body']) !== md5($page2['body']);

        return [
            'ok' => $different,
            'message' => $different
                ? 'les pages diffèrent'
                : 'page 1 et page 2 identiques',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection page très haute retourne bien 404',
    'url' => $base . '/manga/collection/page/9999',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl($base . '/manga/collection/page/9999');
        $body = $response['body'];

        $hasFatal = stripos($body, 'fatal error') !== false
            || stripos($body, 'uncaught exception') !== false
            || stripos($body, 'warning') !== false;

        return [
            'ok' => $response['status'] === 404 && !$hasFatal,
            'message' => $response['status'] === 404
                ? ($hasFatal ? 'sortie anormale détectée' : '404 propre')
                : 'status ' . $response['status'],
        ];
    },
]);

/*
|--------------------------------------------------------------------------
| PAGINATION AJAX
|--------------------------------------------------------------------------
*/

addGetTest($tests, [
    'category' => 'Pagination',
    'label' => 'Collection AJAX page 0 refusée',
    'path' => '/manga/collection-ajax/page/0',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'Pagination',
    'label' => 'Collection AJAX page négative refusée',
    'path' => '/manga/collection-ajax/page/-1',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'Pagination',
    'label' => 'Collection AJAX page très haute refusée',
    'path' => '/manga/collection-ajax/page/9999',
    'expected_status' => 404,
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection AJAX page 1 contient du contenu',
    'url' => $base . '/manga/collection-ajax/page/1',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl($base . '/manga/collection-ajax/page/1');
        $body = $response['body'];

        return [
            'ok' => $response['status'] === 200
                && (
                    stripos($body, 'collection-card') !== false
                    || stripos($body, '<article') !== false
                    || stripos($body, '<a') !== false
                ),
            'message' => $response['status'] === 200
                ? 'contenu AJAX détecté'
                : 'page AJAX inaccessible',
        ];
    },
]);