<?php

declare(strict_types=1);

$tests[] = [

    'category' => 'Errors',

    'label' => 'Route inconnue retourne 404',

    'path' => '/route-inexistante',

    'expected_status' => 404,
];

$tests[] = [

    'category' => 'Errors',

    'label' => 'Série inexistante retourne 404',

    'path' => '/manga/series/serie-qui-nexiste-pas',

    'expected_status' => 404,
];

$tests[] = [

    'category' => 'Errors',

    'label' => 'Manga inexistant retourne 404',

    'path' => '/manga/series/serie-qui-nexiste-pas/999',

    'expected_status' => 404,
];

$tests[] = [

    'category' => 'Errors',

    'label' => 'Page série hors limite retourne 404',

    'path' => '/manga/series/page/999999',

    'expected_status' => 404,
];

$tests[] = [

    'category' => 'Errors',

    'label' => 'Niveau HSK inexistant retourne 404',

    'path' => '/chinois/grammaire/hsk999',

    'expected_status' => 404,
];