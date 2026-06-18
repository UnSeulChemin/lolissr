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
} from '../ui/search-result-builders.js';

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

    let index = 0;

    mangas.forEach(
        (manga) =>
        {
            const item =
                buildMangaResult(
                    manga,
                    rawValue,
                    basePath,
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

    chinois.forEach(
        (result) =>
        {
            const item =
                buildChineseResult(
                    result,
                    basePath,
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

    shortcuts.forEach(
        (shortcut) =>
        {
            const item =
                buildShortcutSearchResult(
                    shortcut,
                    basePath,
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

    if (index === 0)
    {
        closeDropdown(
            searchDropdown,
        );

        return;
    }

    openDropdown(
        searchDropdown,
    );
}