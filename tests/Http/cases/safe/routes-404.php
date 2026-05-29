<?php

declare(strict_types=1);

$tests[] = [

    'category' => '404',

    'label' => 'Route inexistante',

    'path' => '/route-inexistante',

    'expected_status' => 404,
];

$tests[] = [

    'category' => '404',

    'label' => 'Serie inexistante',

    'path' => '/manga/series/serie-qui-nexiste-pas',

    'expected_status' => 404,
];

$tests[] = [

    'category' => '404',

    'label' => 'Manga inexistant',

    'path' => '/manga/series/serie-qui-nexiste-pas/999',

    'expected_status' => 404,
];

$tests[] = [

    'category' => '404',

    'label' => 'Page manga inexistante',

    'path' => '/manga/series/page/999999',

    'expected_status' => 404,
];

$tests[] = [

    'category' => '404',

    'label' => 'HSK inexistant',

    'path' => '/chinois/grammaire/hsk999',

    'expected_status' => 404,
];