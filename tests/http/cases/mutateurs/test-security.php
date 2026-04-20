<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SECURITY TESTS
|--------------------------------------------------------------------------
*/

if (!isset($testAjaxUpdate))
{
    $testAjaxUpdate = false;
}

if ($testAjaxUpdate)
{
    addPostCheck($postChecks, [
        'category' => 'Security',
        'label' => 'Injection HTML commentaire',
        'url' => buildTestUrl($base, '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero),
        'callback' => static function () use ($base, $realSlug, $realNumero): array
        {
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
                ],
                $payload
            );

            $json = decodeJsonResponse($response['body']);

            if ($response['status'] === 422)
            {
                return [
                    'ok' => is_array($json) && ($json['success'] ?? null) === false,
                    'message' => 'HTML dangereux refusé en validation',
                ];
            }

            if ($response['status'] === 403)
            {
                return [
                    'ok' => is_array($json)
                        && ($json['success'] ?? null) === false
                        && isset($json['message'])
                        && is_string($json['message']),
                    'message' => 'écriture bloquée en mode test',
                ];
            }

            return [
                'ok' => false,
                'message' => 'status inattendu ' . $response['status'],
            ];
        },
    ]);
}