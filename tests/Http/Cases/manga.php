<?php

declare(strict_types=1);

// =========================================
// PAGES
// =========================================

$tests[] = ['category' => 'Manga', 'label' => 'Accueil manga', 'path' => '/manga'];
$tests[] = ['category' => 'Manga', 'label' => 'Possession refuse GET', 'path' => '/manga/series/fixture/posseder/2', 'expected_status' => 405];
$tests[] = ['category' => 'Manga', 'label' => 'Possession exige CSRF', 'method' => 'POST', 'path' => '/manga/series/fixture/posseder/2', 'headers' => ['Accept: application/json'], 'expected_status' => 419, 'json' => true];

$tests[] = ['category' => 'Manga', 'label' => 'Liens manga', 'path' => '/manga/lien'];

$tests[] = ['category' => 'Manga', 'label' => 'Liste des séries', 'path' => '/manga/series'];

$tests[] = ['category' => 'Manga', 'label' => 'Pagination séries page 1', 'path' => '/manga/series/page/1'];

$tests[] = ['category' => 'Manga', 'label' => 'Liste des artbooks', 'path' => '/manga/artbooks'];

$tests[] = ['category' => 'Manga', 'label' => 'Pagination artbooks page 1', 'path' => '/manga/artbooks/page/1'];

$tests[] = ['category' => 'Manga', 'label' => 'Notes', 'path' => '/manga/series/notes'];

$tests[] = ['category' => 'Manga', 'label' => 'À lire', 'path' => '/manga/series/a-lire'];

$tests[] = ['category' => 'Manga', 'label' => 'Ajout', 'path' => '/manga/ajouter'];

$tests[] = ['category' => 'Manga', 'label' => 'Ajout manga', 'path' => '/manga/ajouter/manga'];

$tests[] = ['category' => 'Manga', 'label' => 'Ajout artbook', 'path' => '/manga/ajouter/artbook'];

// =========================================
// AJAX HTML
// =========================================

$tests[] = [
    'category' => 'Manga',
    'label' => 'Pagination séries HTML',
    'path' => '/manga/ajax/series/page/1',
    'fragment' => true
];

$tests[] = [
    'category' => 'Manga',
    'label' => 'Pagination artbooks HTML',
    'path' => '/manga/ajax/artbooks/page/1',
    'fragment' => true
];

// =========================================
// AJAX JSON
// =========================================

$tests[] = [
    'category' => 'Manga',
    'label' => 'Recherche JSON',
    'path' => '/manga/ajax/recherche/test',
    'json' => true,
    'header_contains' => ['application/json'],
    'headers' => ['Accept: application/json', 'X-Requested-With: XMLHttpRequest']
];
$tests[] = ['category' => 'Manga', 'label' => 'À paraître', 'path' => '/manga/series/a-paraitre'];
$tests[] = ['category' => 'Manga', 'label' => 'À paraître page 1', 'path' => '/manga/series/a-paraitre/page/1'];
$tests[] = ['category' => 'Manga', 'label' => 'Non possédés', 'path' => '/manga/series/non-possedes'];
$tests[] = ['category' => 'Manga', 'label' => 'Non possédés page 1', 'path' => '/manga/series/non-possedes/page/1'];
$tests[] = ['category' => 'Manga', 'label' => 'Sorties page hors limite', 'path' => '/manga/series/a-paraitre/page/999999', 'expected_status' => 404];

// Recommendation reads: discover actual pages for the authenticated audit account.
$tests[] = ['category' => 'Manga', 'label' => 'Suggestions masquees', 'path' => '/manga/series/recommandations-masquees'];
$tests[] = ['category' => 'Manga', 'label' => 'Suggestions masquees SPA', 'path' => '/manga/series/recommandations-masquees/page/1', 'json' => true, 'headers' => ['Accept: application/json', 'X-Page-Format: fragment']];
$tests[] = ['category' => 'Manga', 'label' => 'Suggestions masquees hors limite', 'path' => '/manga/series/recommandations-masquees/page/999999', 'expected_status' => 404];
$tests[] = ['category' => 'Manga', 'label' => 'Retablir exige CSRF', 'method' => 'POST', 'path' => '/manga/series/recommandations/00000000-0000-0000-0000-000000000001/retablir', 'headers' => ['Accept: application/json'], 'expected_status' => 419, 'json' => true];
$tests[] = ['category' => 'Manga', 'label' => 'Favoris', 'path' => '/manga/series/favoris'];
$tests[] = ['category' => 'Manga', 'label' => 'Favoris SPA', 'path' => '/manga/series/favoris/page/1', 'json' => true, 'headers' => ['Accept: application/json', 'X-Page-Format: fragment']];
$tests[] = ['category' => 'Manga', 'label' => 'Favoris hors limite', 'path' => '/manga/series/favoris/page/999999', 'expected_status' => 404];
$tests[] = ['category' => 'Manga', 'label' => 'Favoris exige CSRF', 'method' => 'POST', 'path' => '/manga/series/recommandations/00000000-0000-0000-0000-000000000001/favoris', 'headers' => ['Accept: application/json'], 'expected_status' => 419, 'json' => true];
$tests[] = ['category' => 'Manga', 'label' => 'Filtre categorie', 'path' => '/manga/series/recommandations/categorie/Sh%C3%B4nen'];
$tests[] = ['category' => 'Manga', 'label' => 'Filtre categorie SPA', 'path' => '/manga/series/recommandations/categorie/Sh%C3%B4nen/page/1', 'json' => true, 'headers' => ['Accept: application/json', 'X-Page-Format: fragment']];
$tests[] = ['category' => 'Manga', 'label' => 'Filtre categorie hors limite', 'path' => '/manga/series/recommandations/categorie/Sh%C3%B4nen/page/999999', 'expected_status' => 404];
$tests[] = ['category' => 'Manga', 'label' => 'Filtre auteur', 'path' => '/manga/series/recommandations-auteurs/auteur/Kei%20Sasuga'];
$tests[] = ['category' => 'Manga', 'label' => 'Filtre auteur SPA', 'path' => '/manga/series/recommandations-auteurs/auteur/Kei%20Sasuga/page/1', 'json' => true, 'headers' => ['Accept: application/json', 'X-Page-Format: fragment']];
$tests[] = ['category' => 'Manga', 'label' => 'Filtre auteur hors limite', 'path' => '/manga/series/recommandations-auteurs/auteur/Kei%20Sasuga/page/999999', 'expected_status' => 404];
foreach (['recommandations' => 'Recommandations catégories', 'recommandations-auteurs' => 'Recommandations auteurs'] as $route => $label)
{
    $path = '/manga/series/' . $route;
    $tests[] = ['category' => 'Manga', 'label' => $label, 'path' => $path];
    $response = http_get(http_base() . $path);
    preg_match_all('~' . preg_quote($path, '~') . '/page/([1-9][0-9]*)~', $response['body'], $matches);
    $pages = array_values(array_unique([1, ...array_map('intval', $matches[1])]));
    sort($pages);
    foreach (array_slice($pages, 0, 56) as $page)
    {
        $pagePath = $path . '/page/' . $page;
        $tests[] = ['category' => 'Manga', 'label' => $label . ' page ' . $page, 'path' => $pagePath];
        $tests[] = ['category' => 'Manga', 'label' => $label . ' SPA page ' . $page, 'path' => $pagePath,
            'json' => true, 'headers' => ['Accept: application/json', 'X-Page-Format: fragment']];
    }
    $tests[] = ['category' => 'Manga', 'label' => $label . ' page hors limite', 'path' => $path . '/page/999999', 'expected_status' => 404];
}
