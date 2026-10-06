// =================================================
// RENDU DE LA RECHERCHE
// =================================================

import { clearSearchResults } from '../ui/search-dropdown.js';

import {
    buildMangaResult,
    buildShortcutSearchResult,
    buildChineseResult,
    buildFigurineResult,
    buildNendoroidResult,
    buildPelucheResult,
    buildArtbookResult
} from '../builders/search-result-builders.js';

import { appendSectionTitle } from './search-section-renderer.js';

// =================================================
// AJOUT SECTION
// =================================================

function appendSection(
    {
        title,
        results,
        buildItem,
        searchResults,
        setupResultItem,
        index
    }
)
{
    if (results.length === 0)
    {
        return index;
    }

    appendSectionTitle(searchResults, title);

    results.forEach(
        (result) =>
        {
            const item = buildItem(result);

            setupResultItem(item, index);

            searchResults.appendChild(item);

            index++;
        }
    );

    return index;
}

// =================================================
// RENDU RÉSULTATS
// =================================================

export function renderResults(
    {
        mangas,
        categories = [],
        authors = [],
        artbooks,
        chinois,
        figurines,
        nendoroids,
        peluches,
        shortcuts,
        rawValue,
        basePath,
        searchResults,
        searchDropdown,
        setupResultItem,
        openDropdown,
        closeDropdown
    }
)
{
    clearSearchResults(searchResults);
    const fragment = document.createDocumentFragment();
    const container = searchResults;
    searchResults = fragment;

    let index = 0;

    index = appendSection({
            title: '📚 SÉRIES',

            results: mangas.slice(0, 5),

            buildItem: (manga) =>
                    buildMangaResult(manga, rawValue, basePath),
            searchResults,
            setupResultItem,
            index
        });

    for (const [title, results] of [['✨ CATÉGORIES MANGA', categories], ['✍️ AUTEURS MANGA', authors]])
    {
        index = appendSection({title, results: results.slice(0, 5),
            buildItem: item => buildShortcutSearchResult(item, basePath, rawValue),
            searchResults, setupResultItem, index});
    }

    index = appendSection({
            title: '📕 ARTBOOKS',

            results: artbooks.slice(0, 5),

            buildItem: (artbook) =>
                    buildArtbookResult(artbook, rawValue, basePath),
            searchResults,
            setupResultItem,
            index
        });

index = appendSection({
            title: '🎀 FIGURINES',

            results: figurines.slice(0, 5),

            buildItem: (figurine) =>
                    buildFigurineResult(figurine, rawValue, basePath),
            searchResults,
            setupResultItem,
            index
        });

    index = appendSection({
            title: '🪆 NENDOROIDS',

            results: nendoroids.slice(0, 5),

            buildItem: (nendoroid) =>
                    buildNendoroidResult(nendoroid, rawValue, basePath),
            searchResults,
            setupResultItem,
            index
        });

    index = appendSection({
            title: '🧸 PELUCHES',

            results: peluches.slice(0, 5),

            buildItem: (peluche) =>
                    buildPelucheResult(peluche, rawValue, basePath),
            searchResults,
            setupResultItem,
            index
        });

    index = appendSection({
            title: '⛩️ CHINOIS',

            results: chinois.slice(0, 5),

            buildItem: (item) =>
                    buildChineseResult(item, basePath),
            searchResults,
            setupResultItem,
            index
        });

    index = appendSection({
            title: '⚡ RACCOURCIS',

            results: shortcuts.slice(0, 5),

            buildItem: (shortcut) =>
                    buildShortcutSearchResult(shortcut, basePath),
            searchResults,
            setupResultItem,
            index
        });

    container.appendChild(fragment);
    if (index === 0)
    {
        closeDropdown(searchDropdown);

        return;
    }

    openDropdown(searchDropdown);
}
