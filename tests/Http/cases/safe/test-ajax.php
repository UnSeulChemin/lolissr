<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS AJAX SAFE
|--------------------------------------------------------------------------
*/

if (!function_exists('ajaxJsonHeaders'))
{
    function ajaxJsonHeaders(string $contentType = 'application/x-www-form-urlencoded'): array
    {
        return [
            'Content-Type: ' . $contentType,
            'X-Requested-With: XMLHttpRequest',
            'Accept: application/json',
        ];
    }
}

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'POST ajouter validation AJAX invalide',
    'url' => $base . '/manga/ajouter',
    'callback' => static function () use ($base): array
    {
        $payload = http_build_query([
            'livre' => '',
            'slug' => '',
            'numero' => '0',
            'commentaire' => str_repeat('x', 1205),
        ]);

        $response = requestUrl(
            $base . '/manga/ajouter',
            'POST',
            ajaxJsonHeaders(),
            $payload
        );

        $json = decodeJsonResponse($response['body']);

        $ok =
            $response['status'] === 422
            && is_array($json)
            && ($json['success'] ?? null) === false
            && (
                !empty($json['errors'])
                || !empty($json['error'])
            );

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status']
                . (is_array($json) ? ' | json erreur reçu' : ' | réponse non json'),
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'POST ajouter body JSON non supporté',
    'url' => $base . '/manga/ajouter',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl(
            $base . '/manga/ajouter',
            'POST',
            ajaxJsonHeaders('application/json'),
            '{"livre":'
        );

        $json = decodeJsonResponse($response['body']);

        $ok =
            in_array($response['status'], [400, 415, 422], true)
            && is_array($json)
            && ($json['success'] ?? null) === false;

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | body JSON non supporté',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'AJAX',
    'label' => 'GET détail contient hooks JS/AJAX',
    'url' => $base . '/manga/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $response = requestUrl($base . '/manga/' . $realSlug . '/' . $realNumero);
        $body = $response['body'];

        $hasHook =
            stripos($body, 'ajax-note') !== false
            || stripos($body, 'ajax-note-button') !== false
            || stripos($body, 'js-detail-card') !== false;

        return [
            'ok' => $response['status'] === 200 && $hasHook,
            'message' => $response['status'] === 200
                ? ($hasHook ? 'hooks AJAX trouvés' : 'hooks AJAX absents')
                : 'page détail inaccessible',
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'GET ajax update-note refusé en 405',
    'url' => $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $response = requestUrl(
            $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
            'GET',
            [
                'X-Requested-With: XMLHttpRequest',
                'Accept: application/json',
            ]
        );

        $json = decodeJsonResponse($response['body']);

        $ok =
            $response['status'] === 405
            && (
                is_array($json)
                || stripos($response['body'], 'Méthode') !== false
            );

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | GET refusé',
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'POST ajax update note manga introuvable',
    'url' => $base . '/manga/ajax/update-note/slug-introuvable-test/999',
    'callback' => static function () use ($base): array
    {
        $payload = http_build_query([
            'jacquette' => '4',
            'livre_note' => '4',
        ]);

        $response = requestUrl(
            $base . '/manga/ajax/update-note/slug-introuvable-test/999',
            'POST',
            ajaxJsonHeaders(),
            $payload
        );

        $json = decodeJsonResponse($response['body']);

        $ok =
            $response['status'] === 404
            && is_array($json)
            && ($json['success'] ?? null) === false;

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | manga introuvable',
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'POST update AJAX URL non canonique',
    'url' => $base . '/manga/modifier/' . $nonCanonicalSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $nonCanonicalSlug, $realNumero): array
    {
        $payload = http_build_query([
            'jacquette' => '4',
            'livre_note' => '4',
            'commentaire' => 'test canonical ajax',
        ]);

        $response = requestUrl(
            $base . '/manga/modifier/' . $nonCanonicalSlug . '/' . $realNumero,
            'POST',
            ajaxJsonHeaders(),
            $payload
        );

        $json = decodeJsonResponse($response['body']);

        $ok =
            $response['status'] === 409
            && is_array($json)
            && ($json['success'] ?? null) === false
            && !empty($json['redirect']);

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | URL non canonique',
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'POST update AJAX manga introuvable',
    'url' => $base . '/manga/modifier/slug-introuvable-test/999',
    'callback' => static function () use ($base): array
    {
        $payload = http_build_query([
            'jacquette' => '4',
            'livre_note' => '4',
            'commentaire' => 'test introuvable',
        ]);

        $response = requestUrl(
            $base . '/manga/modifier/slug-introuvable-test/999',
            'POST',
            ajaxJsonHeaders(),
            $payload
        );

        $json = decodeJsonResponse($response['body']);

        $ok =
            $response['status'] === 404
            && is_array($json)
            && ($json['success'] ?? null) === false;

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | update introuvable',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'AJAX',
    'label' => 'GET search AJAX limité à 6 résultats max',
    'url' => $base . '/manga/search-ajax/a',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl(
            $base . '/manga/search-ajax/a',
            'GET',
            [
                'X-Requested-With: XMLHttpRequest',
                'Accept: application/json',
            ]
        );

        $json = decodeJsonResponse($response['body']);

        $ok =
            $response['status'] === 200
            && is_array($json)
            && count($json) <= 6;

        return [
            'ok' => $ok,
            'message' => $response['status'] === 200
                ? 'résultats: ' . (is_array($json) ? count($json) : 0)
                : 'search AJAX inaccessible',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'AJAX',
    'label' => 'GET search AJAX vide retourne JSON vide',
    'url' => $base . '/manga/search-ajax/-',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl(
            $base . '/manga/search-ajax/-',
            'GET',
            [
                'X-Requested-With: XMLHttpRequest',
                'Accept: application/json',
            ]
        );

        $json = decodeJsonResponse($response['body']);

        $ok =
            $response['status'] === 200
            && is_array($json)
            && $json === [];

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | résultat vide attendu',
        ];
    },
]);