// =========================================
// SEARCH CONTROLLER
// =========================================

import {
    $,
    $$,
} from '../../core/dom.js';

import {
    fetchSearchResults,
} from '../api/search-api.js';

import {
    findSearchShortcuts,
} from '../shortcuts/search-shortcuts.js';

import {
    normalizeSearchQuery,
} from '../utils/search-utils.js';

import {
    openSearchDropdown,
    closeSearchDropdown,
    clearSearchResults,
} from '../ui/search-dropdown.js';

import {
    renderResults,
} from './search-renderer.js';

import {
    updateActiveResult,
} from './search-keyboard.js';

// =========================================
// CONFIG
// =========================================

const SEARCH_DELAY =
    75;

// =========================================
// STATE
// =========================================

let debounceTimer =
    null;

let abortController =
    null;

let activeIndex =
    -1;

// =========================================
// INIT
// =========================================

export function initSearchController()
{
    const search =
        $('.js-header-search');

    const searchInput =
        $('#header-search-input');

    const searchResults =
        $('#header-search-results');

    const searchDropdown =
        $('.js-header-search-dropdown');

    if (
        !search ||
        !searchInput ||
        !searchResults ||
        !searchDropdown
    ) {
        return;
    }

    if (
        search.dataset.initialized ===
        'true'
    ) {
        return;
    }

    search.dataset.initialized =
        'true';

    searchInput.addEventListener(
        'input',
        () =>
        {
            clearTimeout(
                debounceTimer,
            );

            debounceTimer =
                setTimeout(
                    () =>
                    {
                        void handleSearch(
                            search,
                            searchInput,
                            searchResults,
                            searchDropdown,
                        );
                    },
                    SEARCH_DELAY,
                );
        },
    );

    search.addEventListener(
        'submit',
        (
            event,
        ) =>
        {
            event.preventDefault();

            const query =
                normalizeSearchQuery(
                    searchInput.value,
                );

            const basePath =
                search.dataset.basePath
                ?? '/';

            const url =
                query !== ''
                    ? `${basePath}manga/recherche/${encodeURIComponent(query)}`
                    : `${basePath}manga/recherche`;

            window.location.href =
                url;
        },
    );

    searchInput.addEventListener(
        'keydown',
        (
            event,
        ) =>
        {
            handleKeyboardNavigation(
                event,
                searchInput,
                searchResults,
                searchDropdown,
            );
        },
    );

    document.addEventListener(
        'click',
        (
            event,
        ) =>
        {
            if (
                !event.target.closest(
                    '.js-header-search',
                )
            ) {
                resetSearch(
                    searchInput,
                    searchResults,
                    searchDropdown,
                );
            }
        },
    );
}

// =========================================
// HANDLE SEARCH
// =========================================

async function handleSearch(
    search,
    searchInput,
    searchResults,
    searchDropdown,
)
{
    const rawValue =
        searchInput.value;

    const query =
        normalizeSearchQuery(
            rawValue,
        );

    if (
        query === ''
    ) {
        resetSearch(
            searchInput,
            searchResults,
            searchDropdown,
        );

        return;
    }

    abortController?.abort();

    abortController =
        new AbortController();

    activeIndex =
        -1;

    try {

        const basePath =
            search.dataset.basePath
            ?? '/';

        const searchUrl =
            `${basePath}manga/ajax/recherche`;

        const [
            mangas,
            chinois,
            figurines,
            shortcuts,
        ] = await Promise.all([
            fetchSearchResults(
                `${searchUrl}/${encodeURIComponent(query)}`,
                abortController.signal,
            ),

            fetchSearchResults(
                `${basePath}chinois/ajax/recherche/${encodeURIComponent(query)}`,
                abortController.signal,
            ),

            fetchSearchResults(
                `${basePath}figurine/ajax/recherche/${encodeURIComponent(query)}`,
                abortController.signal,
            ),

            findSearchShortcuts(query),
        ]);

        renderResults({
            mangas,
            figurines,
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
        });

    } catch (error) {

        if (
            error?.name ===
            'AbortError'
        ) {
            return;
        }
    }
}

// =========================================
// SETUP ITEM
// =========================================

function setupResultItem(
    item,
    index,
    searchInput,
    searchResults,
    searchDropdown,
)
{
    item.dataset.index =
        index;

    item.addEventListener(
        'mouseenter',
        () =>
        {
            activeIndex =
                index;

            updateActiveResult(
                searchResults,
                activeIndex,
            );
        },
    );

    item.addEventListener(
        'click',
        () =>
        {
            resetSearch(
                searchInput,
                searchResults,
                searchDropdown,
            );
        },
    );
}

// =========================================
// KEYBOARD NAVIGATION
// =========================================

function handleKeyboardNavigation(
    event,
    searchInput,
    searchResults,
    searchDropdown,
)
{
    const resultItems =
        $$(
            '.search-result-item',
            searchResults,
        );

    if (
        event.key ===
        'ArrowDown'
    ) {

        if (
            !resultItems.length
        ) {
            return;
        }

        event.preventDefault();

        activeIndex++;

        if (
            activeIndex >=
            resultItems.length
        ) {
            activeIndex = 0;
        }

        updateActiveResult(
            searchResults,
            activeIndex,
        );

        return;
    }

    if (
        event.key ===
        'ArrowUp'
    ) {

        if (
            !resultItems.length
        ) {
            return;
        }

        event.preventDefault();

        activeIndex--;

        if (
            activeIndex < 0
        ) {
            activeIndex =
                resultItems.length - 1;
        }

        updateActiveResult(
            searchResults,
            activeIndex,
        );

        return;
    }

    if (
        event.key ===
        'Enter'
    ) {

        const activeItem =
            resultItems[
                activeIndex
            ];

        if (
            activeItem
        ) {

            event.preventDefault();

            resetSearch(
                searchInput,
                searchResults,
                searchDropdown,
            );

            window.location.href =
                activeItem.href;
        }
    }

    if (
        event.key ===
        'Escape'
    ) {
        resetSearch(
            searchInput,
            searchResults,
            searchDropdown,
        );
    }
}

// =========================================
// DROPDOWN
// =========================================

function openDropdown(
    searchDropdown,
)
{
    searchDropdown.classList.remove(
        'is-loading',
    );

    openSearchDropdown(
        searchDropdown,
    );
}

function closeDropdown(
    searchDropdown,
)
{
    closeSearchDropdown(
        searchDropdown,
    );

    searchDropdown.classList.remove(
        'is-loading',
    );
}

// =========================================
// RESET SEARCH
// =========================================

function resetSearch(
    searchInput,
    searchResults,
    searchDropdown,
)
{
    abortController?.abort();

    activeIndex =
        -1;

    searchInput.value =
        '';

    clearSearchResults(
        searchResults,
    );

    closeDropdown(
        searchDropdown,
    );
}

// =========================================
// LEGACY EXPORT
// =========================================

export const initSearchManga =
    initSearchController;