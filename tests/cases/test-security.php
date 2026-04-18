<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SECURITY TESTS
|--------------------------------------------------------------------------
*/

addPostCheck($postChecks, [
    'category' => 'Security',
    'label' => 'Injection HTML commentaire',
    'url' => $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $payload = http_build_query([
            'jacquette' => '4',
            'livre_note' => '4',
            'commentaire' => '<script>alert(1)</script>',
        ]);

        $response = requestUrl(
            $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
            'POST',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest',
            ],
            $payload
        );

        return [
            'ok' => in_array($response['status'], [200, 422], true),
            'message' => 'status ' . $response['status'],
        ];
    },
]);