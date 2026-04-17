<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS AJAX
|--------------------------------------------------------------------------
*/

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'POST ajouter validation AJAX',
    'url' => $base . '/manga/ajouter',
    'callback' => static function () use ($base, $testPostAjouter): array
    {
        if (!$testPostAjouter)
        {
            return [
                'ok' => true,
                'warn' => true,
                'message' => 'skippé (option désactivée)',
            ];
        }

        $payload = http_build_query([
            'livre' => 'Test Manga Auto',
            'slug' => 'test-manga-auto',
            'numero' => 999,
            'commentaire' => 'test auto'
        ]);

        $response = requestUrl(
            $base . '/manga/ajouter',
            'POST',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest',
            ],
            $payload
        );

        $json = json_decode($response['body'], true);

        $ok = in_array($response['status'], [200, 201, 422], true);

        if (is_array($json))
        {
            return [
                'ok' => $ok,
                'message' => 'status ' . $response['status'] . ' | json reçu',
            ];
        }

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | réponse non json',
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'GET détail contient hooks AJAX notes',
    'url' => $base . '/manga/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $response = requestUrl($base . '/manga/' . $realSlug . '/' . $realNumero);
        $body = $response['body'];

        $hasHook = stripos($body, 'ajax-note') !== false
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