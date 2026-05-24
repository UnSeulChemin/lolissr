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
    'callback' => static function () use ($base): array {
        $response = requestUrl($base . '/manga/collection');
        $body = $response['body'];

        $hasContent =
            stripos($body, 'collection-card') !== false
            || stripos($body, '<article') !== false
            || stripos($body, '<a') !== false;

        return [
            'ok' => $response['status'] === 200 && $hasContent,
            'message' => 'status ' . $response['status'],
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection page 2 comportement cohérent',
    'url' => $base . '/manga/collection/page/2',
    'callback' => static function () use ($base): array {
        $response = requestUrl($base . '/manga/collection/page/2');
        $body = $response['body'];

        $hasFatal =
            stripos($body, 'fatal error') !== false
            || stripos($body, 'uncaught exception') !== false
            || stripos($body, 'warning') !== false;

        $hasContent =
            stripos($body, 'collection-card') !== false
            || stripos($body, '<article') !== false
            || stripos($body, '<a') !== false;

        return [
            'ok' => !$hasFatal && (
                ($response['status'] === 200 && $hasContent)
                || $response['status'] === 404
            ),
            'message' => 'status ' . $response['status'],
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection page 2 différente de la page 1 si elle existe',
    'url' => $base . '/manga/collection/page/2',
    'callback' => static function () use ($base): array {
        $page1 = requestUrl($base . '/manga/collection');
        $page2 = requestUrl($base . '/manga/collection/page/2');

        if ($page1['status'] !== 200) {
            return [
                'ok' => false,
                'message' => 'page 1 inaccessible',
            ];
        }

        if ($page2['status'] === 404) {
            return [
                'ok' => true,
                'message' => 'page 2 inexistante, test ignoré',
            ];
        }

        if ($page2['status'] !== 200) {
            return [
                'ok' => false,
                'message' => 'status ' . $page2['status'],
            ];
        }

        return [
            'ok' => md5($page1['body']) !== md5($page2['body']),
            'message' => 'comparaison page 1 / page 2',
        ];
    },
]);

/*
|--------------------------------------------------------------------------
| PAGINATION AJAX
|--------------------------------------------------------------------------
*/

$ajaxHeaders = [
    'X-Requested-With: XMLHttpRequest',
    'Accept: text/html',
];

foreach ([
    '0' => 'Collection AJAX page 0 refusée',
    '-1' => 'Collection AJAX page négative refusée',
    '9999' => 'Collection AJAX page très haute refusée',
] as $page => $label) {
    addHtmlCheck($htmlChecks, [
        'category' => 'Pagination',
        'label' => $label,
        'url' => $base . '/manga/collection-ajax/page/' . $page,
        'callback' => static function () use ($base, $page, $ajaxHeaders): array {
            $response = requestUrl(
                $base . '/manga/collection-ajax/page/' . $page,
                'GET',
                $ajaxHeaders
            );

            $json = decodeJsonResponse($response['body']);

            return [
                'ok' => $response['status'] === 404
                    && is_array($json)
                    && ($json['success'] ?? null) === false,
                'message' => 'status ' . $response['status'],
            ];
        },
    ]);
}

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection AJAX page 1 contient du contenu',
    'url' => $base . '/manga/collection-ajax/page/1',
    'callback' => static function () use ($base, $ajaxHeaders): array {
        $response = requestUrl(
            $base . '/manga/collection-ajax/page/1',
            'GET',
            $ajaxHeaders
        );

        $body = $response['body'];

        $hasContent =
            stripos($body, 'collection-card') !== false
            || stripos($body, '<article') !== false
            || stripos($body, '<a') !== false;

        return [
            'ok' => $response['status'] === 200 && $hasContent,
            'message' => 'status ' . $response['status'],
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Pagination',
    'label' => 'Collection AJAX page 2 comportement cohérent',
    'url' => $base . '/manga/collection-ajax/page/2',
    'callback' => static function () use ($base, $ajaxHeaders): array {
        $response = requestUrl(
            $base . '/manga/collection-ajax/page/2',
            'GET',
            $ajaxHeaders
        );

        $body = $response['body'];

        $hasFatal =
            stripos($body, 'fatal error') !== false
            || stripos($body, 'uncaught exception') !== false
            || stripos($body, 'warning') !== false;

        $hasContent =
            stripos($body, 'collection-card') !== false
            || stripos($body, '<article') !== false
            || stripos($body, '<a') !== false;

        return [
            'ok' => !$hasFatal && (
                ($response['status'] === 200 && $hasContent)
                || $response['status'] === 404
            ),
            'message' => 'status ' . $response['status'],
        ];
    },
]);