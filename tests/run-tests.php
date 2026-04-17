<?php

declare(strict_types=1);

$base = 'http://localhost/lolissr';

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/

$realSlug = 'One-Piece';
$realNumero = 1;

/*
|--------------------------------------------------------------------------
| OPTIONS
|--------------------------------------------------------------------------
|
| Mets à true seulement si ton app DOIT forcer la redirection canonique.
|
*/

$testCanonicalRedirect = false;

/*
|--------------------------------------------------------------------------
| TESTS
|--------------------------------------------------------------------------
*/

$tests = [

    [
        'label' => 'Accueil',
        'path' => '/',
        'expected_status' => 200,
        'must_contain' => ['<body'],
    ],

    [
        'label' => 'Dashboard manga',
        'path' => '/manga',
        'expected_status' => 200,
        'must_contain' => ['Manga'],
    ],

    [
        'label' => 'Collection',
        'path' => '/manga/collection',
        'expected_status' => 200,
        'must_contain' => ['Collection'],
    ],

    [
        'label' => 'Pagination collection page 2',
        'path' => '/manga/collection/page/2',
        'expected_status' => 200,
    ],

    [
        'label' => 'Recherche',
        'path' => '/manga/recherche',
        'expected_status' => 200,
    ],

    [
        'label' => 'Ajouter',
        'path' => '/manga/ajouter',
        'expected_status' => 200,
        'must_contain' => ['<form', 'Livre', 'slug', 'numero'],
    ],

    [
        'label' => 'Page lien',
        'path' => '/manga/lien',
        'expected_status' => 200,
    ],

    [
        'label' => 'Série existante',
        'path' => '/manga/serie/' . $realSlug,
        'expected_status' => 200,
    ],

    [
        'label' => 'Tome existant',
        'path' => '/manga/' . $realSlug . '/' . $realNumero,
        'expected_status' => 200,
    ],

    [
        'label' => 'Page modifier',
        'path' => '/manga/update/' . $realSlug . '/' . $realNumero,
        'expected_status' => 200,
        'must_contain' => ['<form'],
    ],

    [
        'label' => '404 inexistante',
        'path' => '/page-introuvable',
        'expected_status' => 404,
    ],
];

if ($testCanonicalRedirect)
{
    $tests[] = [
        'label' => 'Slug canonique redirect',
        'path' => '/manga/One-Piece/' . $realNumero,
        'expected_status' => 301,
        'expected_location_contains' => '/manga/' . $realSlug . '/' . $realNumero,
    ];
}

/*
|--------------------------------------------------------------------------
| HTTP REQUEST
|--------------------------------------------------------------------------
*/

function requestUrl(string $url): array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true,
            'follow_location' => 0,
            'max_redirects' => 0,
            'timeout' => 10,
            'header' => "User-Agent: LoliSSR-TestRunner\r\n",
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    $headers = $http_response_header ?? [];

    $status = 0;
    $location = null;

    if (!empty($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $matches))
    {
        $status = (int) $matches[1];
    }

    foreach ($headers as $header)
    {
        if (stripos($header, 'Location:') === 0)
        {
            $location = trim(substr($header, 9));
            break;
        }
    }

    return [
        'status' => $status,
        'body' => is_string($body) ? $body : '',
        'headers' => $headers,
        'location' => $location,
        'url' => $url,
    ];
}

/*
|--------------------------------------------------------------------------
| HTML HELPERS
|--------------------------------------------------------------------------
*/

function containsAll(string $html, array $needles): array
{
    foreach ($needles as $needle)
    {
        if (stripos($html, $needle) === false)
        {
            return [false, $needle];
        }
    }

    return [true, null];
}

function extractTitle(string $html): ?string
{
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches))
    {
        return trim(strip_tags($matches[1]));
    }

    return null;
}

function countOccurrences(string $html, string $needle): int
{
    return substr_count(strtolower($html), strtolower($needle));
}

/*
|--------------------------------------------------------------------------
| ASSERTIONS
|--------------------------------------------------------------------------
*/

function runTest(string $base, array $test): array
{
    $url = $base . $test['path'];
    $response = requestUrl($url);

    $expectedStatus = $test['expected_status'];
    $status = $response['status'];
    $body = $response['body'];

    if ($status !== $expectedStatus)
    {
        return [
            'ok' => false,
            'message' => "status $status attendu $expectedStatus",
        ];
    }

    if (!empty($test['must_contain']))
    {
        [$containsAllNeedles, $missingNeedle] = containsAll($body, $test['must_contain']);

        if (!$containsAllNeedles)
        {
            return [
                'ok' => false,
                'message' => 'texte absent : "' . $missingNeedle . '"',
            ];
        }
    }

    if (!empty($test['expected_location_contains']))
    {
        $location = $response['location'] ?? '';

        if ($location === '' || stripos($location, $test['expected_location_contains']) === false)
        {
            return [
                'ok' => false,
                'message' => 'redirect location invalide',
            ];
        }
    }

    return [
        'ok' => true,
        'message' => (string) $status,
        'response' => $response,
    ];
}

/*
|--------------------------------------------------------------------------
| EXTRA CHECKS
|--------------------------------------------------------------------------
*/

function runExtraChecks(string $base, string $realSlug, int $realNumero): array
{
    $checks = [];

    /*
    |--------------------------------------------------------------------------
    | Détail manga : vérifs HTML utiles
    |--------------------------------------------------------------------------
    */

    $detail = requestUrl($base . '/manga/' . $realSlug . '/' . $realNumero);

    if ($detail['status'] === 200)
    {
        $title = extractTitle($detail['body']);

        $checks[] = [
            'label' => 'Detail a un <title>',
            'ok' => !empty($title),
            'message' => $title ?: 'title absent',
        ];

        $checks[] = [
            'label' => 'Detail contient au moins une image',
            'ok' => preg_match('/<img\b/i', $detail['body']) === 1,
            'message' => preg_match('/<img\b/i', $detail['body']) === 1 ? 'img trouvée' : 'aucune image',
        ];

        $checks[] = [
            'label' => 'Detail contient au moins un lien',
            'ok' => preg_match('/<a\b/i', $detail['body']) === 1,
            'message' => preg_match('/<a\b/i', $detail['body']) === 1 ? 'lien trouvé' : 'aucun lien',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Ajouter : présence de champs importants
    |--------------------------------------------------------------------------
    */

    $ajouter = requestUrl($base . '/manga/ajouter');

    if ($ajouter['status'] === 200)
    {
        $requiredFields = [
            'name="livre"',
            'name="slug"',
            'name="numero"',
        ];

        foreach ($requiredFields as $field)
        {
            $checks[] = [
                'label' => 'Ajouter contient ' . $field,
                'ok' => stripos($ajouter['body'], $field) !== false,
                'message' => stripos($ajouter['body'], $field) !== false ? 'ok' : 'absent',
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Modifier : présence de champs notes/commentaire
    |--------------------------------------------------------------------------
    */

    $modifier = requestUrl($base . '/manga/update/' . $realSlug . '/' . $realNumero);

    if ($modifier['status'] === 200)
    {
        $possibleFields = [
            'name="jacquette"',
            'name="livre_note"',
            'name="commentaire"',
        ];

        foreach ($possibleFields as $field)
        {
            $checks[] = [
                'label' => 'Modifier contient ' . $field,
                'ok' => stripos($modifier['body'], $field) !== false,
                'message' => stripos($modifier['body'], $field) !== false ? 'ok' : 'absent',
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Collection : nombre de cards / liens
    |--------------------------------------------------------------------------
    */

    $collection = requestUrl($base . '/manga/collection');

    if ($collection['status'] === 200)
    {
        $linkCount = countOccurrences($collection['body'], '<a');

        $checks[] = [
            'label' => 'Collection contient plusieurs liens',
            'ok' => $linkCount >= 3,
            'message' => $linkCount . ' lien(s)',
        ];
    }

    return $checks;
}

/*
|--------------------------------------------------------------------------
| RUNNER
|--------------------------------------------------------------------------
*/

echo "\n=== TESTS LoliSSR ===\n\n";

$total = 0;
$success = 0;

foreach ($tests as $test)
{
    $total++;

    $result = runTest($base, $test);

    if ($result['ok'])
    {
        echo "✅ OK   " . $test['label'] . " -> " . $test['path'] . " (" . $result['message'] . ")\n";
        $success++;
    }
    else
    {
        echo "❌ FAIL " . $test['label'] . " -> " . $test['path'] . " [" . $result['message'] . "]\n";
    }
}

echo "\n=== CHECKS HTML / STRUCTURE ===\n\n";

$extraChecks = runExtraChecks($base, $realSlug, $realNumero);

foreach ($extraChecks as $check)
{
    $total++;

    if ($check['ok'])
    {
        echo "✅ OK   " . $check['label'] . " (" . $check['message'] . ")\n";
        $success++;
    }
    else
    {
        echo "❌ FAIL " . $check['label'] . " [" . $check['message'] . "]\n";
    }
}

echo "\n---------------------\n";
echo "Résultat : $success / $total tests OK\n\n";