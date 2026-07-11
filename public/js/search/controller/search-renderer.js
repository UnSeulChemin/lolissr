// =========================================
// SEARCH RENDERER
// =========================================

import {
    clearSearchResults,
} from '../ui/search-dropdown.js';

import {
    buildMangaResult,
    buildShortcutSearchResult,
    buildChineseResult,
    buildFigurineResult,
    buildNendoroidResult,
    buildArtbookResult,
} from '../builders/search-result-builders.js';

import {
    appendSectionTitle,
} from '../renderers/search-section-renderer.js';

// =========================================
// APPEND SECTION
// =========================================

function appendSection(
    {
        title,
        results,
        buildItem,
        searchInput,
        searchResults,
        searchDropdown,
        setupResultItem,
        index,
    },
)
{
    if (
        results.length === 0
    ) {
        return index;
    }

    appendSectionTitle(
        searchResults,
        title,
    );

    results.forEach(
        (result) =>
        {
            const item =
                buildItem(
                    result,
                );

            setupResultItem(
                item,
                index,
                searchInput,
                searchResults,
                searchDropdown,
            );

            searchResults.appendChild(
                item,
            );

            index++;
        },
    );

    return index;
}

// =========================================
// RENDER RESULTS
// =========================================

export function renderResults(
    {
        mangas,
        artbooks,
        chinois,
        figurines,
        nendoroids,
        shortcuts,
        rawValue,
        basePath,
        searchInput,
        searchResults,
        searchDropdown,
        setupResultItem,
        openDropdown,
        closeDropdown,
    },
)
{
    clearSearchResults(
        searchResults,
    );

    let index =
        0;

    index =
        appendSection({
            title:
                '📚 SÉRIES',

            results:
                mangas.slice(
                    0,
                    5,
                ),

            buildItem:
                (manga) =>
                    buildMangaResult(
                        manga,
                        rawValue,
                        basePath,
                    ),

            searchInput,
            searchResults,
            searchDropdown,
            setupResultItem,
            index,
        });

    index =
        appendSection({
            title:
                '📕 ARTBOOKS',

            results:
                artbooks.slice(
                    0,
                    5,
                ),

            buildItem:
                (artbook) =>
                    buildArtbookResult(
                        artbook,
                        rawValue,
                        basePath,
                    ),

            searchInput,
            searchResults,
            searchDropdown,
            setupResultItem,
            index,
        });

    index =
        appendSection({
            title:
                '⛩️ CHINOIS',

            results:
                chinois.slice(
                    0,
                    5,
                ),

            buildItem:
                (item) =>
                    buildChineseResult(
                        item,
                        basePath,
                    ),

            searchInput,
            searchResults,
            searchDropdown,
            setupResultItem,
            index,
        });

    index =
        appendSection({
            title:
                '🎀 FIGURINES',

            results:
                figurines.slice(
                    0,
                    5,
                ),

            buildItem:
                (figurine) =>
                    buildFigurineResult(
                        figurine,
                        rawValue,
                        basePath,
                    ),

            searchInput,
            searchResults,
            searchDropdown,
            setupResultItem,
            index,
        });

    index =
        appendSection({
            title:
                '🪆 NENDOROIDS',

            results:
                nendoroids.slice(
                    0,
                    5,
                ),

            buildItem:
                (nendoroid) =>
                    buildNendoroidResult(
                        nendoroid,
                        rawValue,
                        basePath,
                    ),

            searchInput,
            searchResults,
            searchDropdown,
            setupResultItem,
            index,
        });

    index =
        appendSection({
            title:
                '⚡ RACCOURCIS',

            results:
                shortcuts.slice(
                    0,
                    5,
                ),

            buildItem:
                (shortcut) =>
                    buildShortcutSearchResult(
                        shortcut,
                        basePath,
                    ),

            searchInput,
            searchResults,
            searchDropdown,
            setupResultItem,
            index,
        });

    if (
        index === 0
    ) {
        closeDropdown(
            searchDropdown,
        );

        return;
    }

    openDropdown(
        searchDropdown,
    );
}