<?php

declare(strict_types=1);

if (!isset($testAjaxUpdate)) {
    $testAjaxUpdate = false;
}

if ($testAjaxUpdate) {
    addPostCheck($postChecks, [
        'category' => 'Security',
        'label' => 'Injection HTML commentaire',
        'url' => buildTestUrl($base, '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero),
        'callback' => static function () use ($base, $realSlug, $realNumero): array {
            $payload = http_build_query([
                'jacquette' => '4',
                'livre_note' => '4',
                'commentaire' => '<script>alert(1)</script>',
            ]);

            $response = requestUrl(
                buildTestUrl($base, '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero),
                'POST',
                [
                    'Content-Type: application/x-www-form-urlencoded',
                    'X-Requested-With: XMLHttpRequest',
                    'Accept: application/json',
                ],
                $payload
            );

            $json = decodeJsonResponse($response['body']);

            return [
                'ok' => in_array($response['status'], [403, 422], true)
                    && is_array($json)
                    && ($json['success'] ?? null) === false,
                'message' => 'status ' . $response['status'],
            ];
        },
    ]);
}