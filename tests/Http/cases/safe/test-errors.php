<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS ERREURS
|--------------------------------------------------------------------------
*/

addGetTest($tests, [
    'category' => 'Erreurs',
    'label' => 'Série inexistante',
    'path' => '/manga/serie/slug-inexistante-test',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'Erreurs',
    'label' => 'Détail inexistant',
    'path' => '/manga/slug-inexistant-test/999999',
    'expected_status' => 404,
]);