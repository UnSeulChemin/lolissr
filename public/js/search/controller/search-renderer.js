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
        chinois,
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