// =========================================
// SEARCH CONTROLLER
// =========================================

import {
    $,
    $$,
} from '../../../core/dom.js';

import {
    fetchSearchResults,
} from '../api/search-api.js';

import {
    buildMangaSearchResult,
    buildShortcutSearchResult,
} from '../ui/search-builders.js';

import {
    findSearchShortcuts,
} from '../shortcuts/search-shortcuts.js';

import {
    normalizeSearchQuery,
} from '../utils/search-utils.js';

import {
    clearSearchResults,
} from '../ui/search-dropdown.js';

// =========================================
// CONFIG
// =========================================

const SEARCH_DELAY =
    150;

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

    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    if (
        !search ||
        !searchInput ||
        !searchResults ||
        !searchDropdown
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | PREVENT DOUBLE INIT
    |--------------------------------------------------------------------------
    */

    if (
        search.dataset.initialized ===
        'true'
    ) {

        return;
    }

    search.dataset.initialized =
        'true';

    /*
    |--------------------------------------------------------------------------
    | INPUT
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | KEYBOARD
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | OUTSIDE CLICK
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | EMPTY
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | ABORT PREVIOUS
    |--------------------------------------------------------------------------
    */

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
            shortcuts,
        ] = await Promise.all([
            fetchSearchResults(
                `${searchUrl}/${encodeURIComponent(query)}`,
                abortController.signal,
            ),

            findSearchShortcuts(
                query,
            ),
        ]);

        renderResults(
            {
                mangas,
                shortcuts,
                rawValue,
                basePath,
                searchInput,
                searchResults,
                searchDropdown,
            },
        );

    } catch (error) {

        /*
        |--------------------------------------------------------------------------
        | SEARCH MUST NEVER CRASH UI
        |--------------------------------------------------------------------------
        */

        if (
            error?.name
            === 'AbortError'
        ) {

            return;
        }
    }
}

// =========================================
// RENDER RESULTS
// =========================================

function renderResults(
    {
        mangas,
        shortcuts,
        rawValue,
        basePath,
        searchInput,
        searchResults,
        searchDropdown,
    },
)
{
    clearSearchResults(
        searchResults,
    );

    let index =
        0;

    /*
    |--------------------------------------------------------------------------
    | MANGAS
    |--------------------------------------------------------------------------
    */

    mangas.forEach(
        (
            manga,
        ) =>
        {
            const item =
                buildMangaSearchResult(
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

    /*
    |--------------------------------------------------------------------------
    | SHORTCUTS
    |--------------------------------------------------------------------------
    */

    shortcuts.forEach(
        (
            shortcut,
        ) =>
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

    /*
    |--------------------------------------------------------------------------
    | EMPTY
    |--------------------------------------------------------------------------
    */

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
        !resultItems.length
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | DOWN
    |--------------------------------------------------------------------------
    */

    if (
        event.key ===
        'ArrowDown'
    ) {

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
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UP
    |--------------------------------------------------------------------------
    */

    if (
        event.key ===
        'ArrowUp'
    ) {

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
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ENTER
    |--------------------------------------------------------------------------
    */

    if (
        event.key ===
        'Enter'
    ) {

        event.preventDefault();

        const activeItem =
            resultItems[
                activeIndex
            ];

        if (
            !activeItem
        ) {

            return;
        }

        resetSearch(
            searchInput,
            searchResults,
            searchDropdown,
        );

        window.location.href =
            activeItem.href;
    }

    /*
    |--------------------------------------------------------------------------
    | ESCAPE
    |--------------------------------------------------------------------------
    */

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
// UPDATE ACTIVE
// =========================================

function updateActiveResult(
    searchResults,
)
{
    const items =
        $$(
            '.search-result-item',
            searchResults,
        );

    items.forEach(
        (
            item,
        ) =>
        {
            item.classList.remove(
                'is-active',
            );
        },
    );

    const activeItem =
        items[
            activeIndex
        ];

    if (
        activeItem
    ) {

        activeItem.classList.add(
            'is-active',
        );

        activeItem.scrollIntoView(
            {
                block:
                    'nearest',
            },
        );
    }
}

// =========================================
// OPEN DROPDOWN
// =========================================

function openDropdown(
    searchDropdown,
)
{
    searchDropdown.classList.remove(
        'is-loading',
    );

    searchDropdown.classList.add(
        'has-results',
    );
}

// =========================================
// CLOSE DROPDOWN
// =========================================

function closeDropdown(
    searchDropdown,
)
{
    searchDropdown.classList.remove(
        'has-results',
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