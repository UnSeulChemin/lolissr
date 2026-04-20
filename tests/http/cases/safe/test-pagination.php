<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS PAGINATION
|--------------------------------------------------------------------------
*/

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

        $hasContent =
            stripos($body, 'collection-card') !== false
            || stripos($body, '<article') !== false
            || stripos($body, '<a') !== false;

        return [
            'ok' => $response['status'] === 200 && $hasContent,
            'message' => $response['status'] === 200
                ? ($hasContent ? 'contenu détecté' : 'aucun contenu détecté')
                : 'page inaccessible',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection page 2 comportement cohérent',
    'url' => $base . '/manga/collection/page/2',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl($base . '/manga/collection/page/2');
        $body = $response['body'];

        $hasContent =
            stripos($body, 'collection-card') !== false
            || stripos($body, '<article') !== false
            || stripos($body, '<a') !== false;

        $hasFatal =
            stripos($body, 'fatal error') !== false
            || stripos($body, 'uncaught exception') !== false
            || stripos($body, 'warning') !== false;

        $ok =
            ($response['status'] === 200 && $hasContent && !$hasFatal)
            || ($response['status'] === 404 && !$hasFatal);

        return [
            'ok' => $ok,
            'message' => $response['status'] === 200
                ? ($hasContent ? 'page 2 valide avec contenu' : 'page 2 vide')
                : ($response['status'] === 404
                    ? 'page 2 absente proprement'
                    : 'status ' . $response['status']),
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection page 2 différente de la page 1 si elle existe',
    'url' => $base . '/manga/collection/page/2',
    'callback' => static function () use ($base): array
    {
        $page1 = requestUrl($base . '/manga/collection');
        $page2 = requestUrl($base . '/manga/collection/page/2');

        if ($page1['status'] !== 200)
        {
            return [
                'ok' => false,
                'message' => 'page 1 inaccessible',
            ];
        }

        if ($page2['status'] === 404)
        {
            return [
                'ok' => true,
                'message' => 'page 2 inexistante, test non applicable',
            ];
        }

        if ($page2['status'] !== 200)
        {
            return [
                'ok' => false,
                'message' => 'page 2 inaccessible',
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

        $hasFatal =
            stripos($body, 'fatal error') !== false
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
        $response = requestUrl(
            $base . '/manga/collection-ajax/page/1',
            'GET',
            [
                'X-Requested-With: XMLHttpRequest',
            ]
        );

        $body = $response['body'];

        $hasContent =
            stripos($body, 'collection-card') !== false
            || stripos($body, '<article') !== false
            || stripos($body, '<a') !== false;

        return [
            'ok' => $response['status'] === 200 && $hasContent,
            'message' => $response['status'] === 200
                ? ($hasContent ? 'contenu AJAX détecté' : 'aucun contenu AJAX détecté')
                : 'page AJAX inaccessible',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection AJAX page 2 comportement cohérent',
    'url' => $base . '/manga/collection-ajax/page/2',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl(
            $base . '/manga/collection-ajax/page/2',
            'GET',
            [
                'X-Requested-With: XMLHttpRequest',
            ]
        );

        $body = $response['body'];

        $hasContent =
            stripos($body, 'collection-card') !== false
            || stripos($body, '<article') !== false
            || stripos($body, '<a') !== false;

        $hasFatal =
            stripos($body, 'fatal error') !== false
            || stripos($body, 'uncaught exception') !== false
            || stripos($body, 'warning') !== false;

        $ok =
            ($response['status'] === 200 && $hasContent && !$hasFatal)
            || ($response['status'] === 404 && !$hasFatal);

        return [
            'ok' => $ok,
            'message' => $response['status'] === 200
                ? ($hasContent ? 'page AJAX 2 valide avec contenu' : 'page AJAX 2 vide')
                : ($response['status'] === 404
                    ? 'page AJAX 2 absente proprement'
                    : 'status ' . $response['status']),
        ];
    },
]);