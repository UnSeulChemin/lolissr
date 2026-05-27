// =========================================
// SEARCH CONTROLLER
// =========================================

import {
    $,
    $$,
} from '../../../core/dom.js';

import {
    navigateTo,
} from '../../../router/router.js';

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
    openSearchDropdown,
    closeSearchDropdown,
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
    const searchInput =
        $(
            '[data-search-input]',
        );

    const searchResults =
        $(
            '[data-search-results]',
        );

    if (
        !searchInput ||
        !searchResults
    ) {
        return;
    }

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
                        handleSearch(
                            searchInput,
                            searchResults,
                        );
                    },
                    SEARCH_DELAY,
                );
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
                    '[data-search]',
                )
            ) {
                closeSearchDropdown(
                    searchResults,
                );
            }
        },
    );
}

// =========================================
// HANDLE SEARCH
// =========================================

async function handleSearch(
    searchInput,
    searchResults,
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
        clearSearchResults(
            searchResults,
        );

        closeSearchDropdown(
            searchResults,
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
            document.body.dataset.basepath ??
            '/';

        const [
            mangas,
            shortcuts,
        ] = await Promise.all([
            fetchSearchResults(
                `${basePath}search?q=${encodeURIComponent(query)}`,
                abortController.signal,
            ),

            Promise.resolve(
                findSearchShortcuts(
                    query,
                ),
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
            },
        );

    } catch (error) {

        if (
            error?.name !==
            'AbortError'
        ) {
            console.error(
                '[SEARCH]',
                error,
            );
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
    },
)
{
    clearSearchResults(
        searchResults,
    );

    const fragment =
        document.createDocumentFragment();

    mangas.forEach(
        (
            manga,
            index,
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
            );

            fragment.appendChild(
                item,
            );
        },
    );

    shortcuts.forEach(
        (
            shortcut,
            shortcutIndex,
        ) =>
        {
            const index =
                mangas.length +
                shortcutIndex;

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
            );

            fragment.appendChild(
                item,
            );
        },
    );

    if (
        !fragment.children.length
    ) {
        closeSearchDropdown(
            searchResults,
        );

        return;
    }

    searchResults.appendChild(
        fragment,
    );

    openSearchDropdown(
        searchResults,
    );
}

// =========================================
// SETUP RESULT ITEM
// =========================================

function setupResultItem(
    item,
    index,
    searchInput,
    searchResults,
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
        (
            event,
        ) =>
        {
            event.preventDefault();

            resetSearch(
                searchInput,
                searchResults,
            );

            navigateTo(
                item.href,
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

    // DOWN

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

    // UP

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

    // ENTER

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
        );

        navigateTo(
            activeItem.href,
        );
    }

    // ESCAPE

    if (
        event.key ===
        'Escape'
    ) {
        closeSearchDropdown(
            searchResults,
        );
    }
}

// =========================================
// UPDATE ACTIVE RESULT
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
// RESET SEARCH
// =========================================

function resetSearch(
    searchInput,
    searchResults,
)
{
    searchInput.value =
        '';

    activeIndex =
        -1;

    clearSearchResults(
        searchResults,
    );

    closeSearchDropdown(
        searchResults,
    );
}