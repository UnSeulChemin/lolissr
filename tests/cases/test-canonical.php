<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS CANONICAL
|--------------------------------------------------------------------------
*/

if ($testCanonicalRedirect)
{
    /*
    |----------------------------------------------------------------------
    | NOTE
    |----------------------------------------------------------------------
    | Active ce test seulement si ton action serie() redirige bien le slug
    | non canonique vers le slug canonique.
    */
    /*
    addGetTest($tests, [
        'category' => 'Canonical',
        'label' => 'Redirect canonique série',
        'path' => '/manga/serie/' . $nonCanonicalSlug,
        'expected_status' => 301,
        'expected_location_contains' => '/manga/serie/' . $realSlug,
    ]);
    */

    addGetTest($tests, [
        'category' => 'Canonical',
        'label' => 'Redirect canonique tome',
        'path' => '/manga/' . $nonCanonicalSlug . '/' . $realNumero,
        'expected_status' => 301,
        'expected_location_contains' => '/manga/' . $realSlug . '/' . $realNumero,
    ]);

    addGetTest($tests, [
        'category' => 'Canonical',
        'label' => 'Redirect canonique modifier',
        'path' => '/manga/update/' . $nonCanonicalSlug . '/' . $realNumero,
        'expected_status' => 301,
        'expected_location_contains' => '/manga/update/' . $realSlug . '/' . $realNumero,
    ]);
}
else
{
    addHtmlCheck($htmlChecks, [
        'category' => 'Canonical',
        'label' => 'Redirect canonique désactivé',
        'url' => null,
        'callback' => static function (): array
        {
            return [
                'ok' => true,
                'message' => 'test canonical désactivé dans la config',
            ];
        },
    ]);
}