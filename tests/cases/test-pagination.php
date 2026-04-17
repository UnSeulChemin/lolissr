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

        return [
            'ok' => md5($page1['body']) !== md5($page2['body']),
            'message' => md5($page1['body']) !== md5($page2['body'])
                ? 'les pages diffèrent'
                : 'page 1 et page 2 identiques',
        ];
    },
]);