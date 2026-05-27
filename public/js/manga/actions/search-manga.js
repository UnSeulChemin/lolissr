// =========================================
// SEARCH MANGA
// =========================================

import {
    $,
    $$,
} from '../../core/dom.js';

import {
    normalizeSearchQuery,
} from '../utils/slug.js';

import {
    findSearchShortcuts,
} from '../../core/keyboard/search-shortcuts.js';

import {
    fetchSearchResults,
} from '../search/search-api.js';

import {
    buildMangaSearchResult,
    buildShortcutSearchResult,
} from '../search/search-builders.js';

import {
    navigateTo,
} from '../../navigation/ajax-navigation.js';

import {
    debug,
    debugError,
} from '../../core/debug.js';

// =========================================
// CONFIG
// =========================================

const SEARCH_DEBOUNCE =
    250;

const MIN_SEARCH_LENGTH =
    2;

// =========================================
// STATE
// =========================================

let initialized =
    false;

let debounceTimer =
    null;

let abortController =
    null;

let activeIndex =
    -1;

// =========================================
// ELEMENTS
// =========================================

let form =
    null;

let input =
    null;

let results =
    null;

let dropdown =
    null;

let basePath =
    '/';

// =========================================
// HELPERS
// =========================================

function getItems()
{
    return $$(
        '.search-result-item',
        results,
    );
}

function resetActive()
{
    activeIndex =
        -1;

    getItems().forEach(
        (
            item,
        ) =>
        {
            item.classList.remove(
                'is-active',
            );
        },
    );
}

function updateActive()
{
    const items =
        getItems();

    items.forEach(
        (
            item,
            index,
        ) =>
        {
            item.classList.toggle(
                'is-active',
                index === activeIndex,
            );
        },
    );

    items[
        activeIndex
    ]?.scrollIntoView({
        block:
            'nearest',
    });
}

function openDropdown()
{
    dropdown?.classList.add(
        'has-results',
    );
}

function closeDropdown()
{
    if (
        !results
        || !dropdown
    ) {
        return;
    }

    results.innerHTML =
        '';

    dropdown.classList.remove(
        'is-loading',
        'has-results',
    );

    abortController?.abort();

    abortController =
        null;

    resetActive();
}

function setLoading(
    state,
)
{
    dropdown?.classList.toggle(
        'is-loading',
        state,
    );
}

function renderEmptyState(
    message =
        'Aucun résultat trouvé',
)
{
    results.innerHTML =
    `
        <div class="search-result-empty">
            ${message}
        </div>
    `;

    openDropdown();

    resetActive();
}

// =========================================
// SEARCH
// =========================================

async function performSearch(
    rawValue,
)
{
    abortController?.abort();

    const normalized =
        normalizeSearchQuery(
            rawValue,
        );

    if (
        rawValue.length
            < MIN_SEARCH_LENGTH
        || normalized === ''
    ) {

        closeDropdown();

        return;
    }

    abortController =
        new AbortController();

    const currentController =
        abortController;

    try {

        setLoading(
            true,
        );

        results.innerHTML =
            '';

        resetActive();

        // =================================
        // SHORTCUTS
        // =================================

        const shortcuts =
            findSearchShortcuts(
                rawValue,
            );

        shortcuts.forEach(
            (
                shortcut,
            ) =>
            {
                results.appendChild(
                    buildShortcutSearchResult(
                        shortcut,
                        basePath,
                    ),
                );
            },
        );

        // =================================
        // API
        // =================================

        const data =
            await fetchSearchResults(
                `${basePath}manga/ajax/recherche/${encodeURIComponent(normalized)}`,
                currentController.signal,
            );

        const mangas =
            data?.data?.results
            || [];

        if (
            !mangas.length
            && !shortcuts.length
        ) {

            renderEmptyState();

            return;
        }

        mangas.forEach(
            (
                manga,
            ) =>
            {
                results.appendChild(
                    buildMangaSearchResult(
                        manga,
                        rawValue,
                        basePath,
                    ),
                );
            },
        );

        openDropdown();

        activeIndex =
            0;

        updateActive();

    } catch (error) {

        if (
            error?.name
            === 'AbortError'
        ) {
            return;
        }

        debugError(
            'SEARCH',
            error,
        );

        renderEmptyState(
            'Erreur de chargement',
        );

    } finally {

        if (
            currentController.signal.aborted
        ) {
            return;
        }

        setLoading(
            false,
        );
    }
}

// =========================================
// KEYBOARD
// =========================================

function handleKeyboard(
    event,
)
{
    if (
        !dropdown?.classList.contains(
            'has-results',
        )
    ) {
        return;
    }

    const items =
        getItems();

    if (!items.length) {
        return;
    }

    switch (event.key)
    {
        case 'ArrowDown':

            event.preventDefault();

            activeIndex =
                Math.min(
                    activeIndex + 1,
                    items.length - 1,
                );

            updateActive();

            break;

        case 'ArrowUp':

            event.preventDefault();

            activeIndex =
                Math.max(
                    activeIndex - 1,
                    0,
                );

            updateActive();

            break;

        case 'Enter':

            if (
                activeIndex < 0
            ) {
                return;
            }

            event.preventDefault();

            {
                const item =
                    items[
                        activeIndex
                    ];

                if (
                    item
                    instanceof HTMLAnchorElement
                ) {

                    closeDropdown();

                    void navigateTo(
                        item.href,
                    );
                }
            }

            break;

        case 'Escape':

            closeDropdown();

            break;

        default:
            break;
    }
}

// =========================================
// INIT
// =========================================

export function initSearchManga()
{
    form =
        $('.js-header-search');

    input =
        $('#header-search-input');

    results =
        $('#header-search-results');

    dropdown =
        $('.js-header-search-dropdown');

    if (
        !form
        || !input
        || !results
        || !dropdown
    ) {
        return;
    }

    // =====================================
    // REBIND SAFE
    // =====================================

    if (
        initialized
    ) {

        debug(
            'SEARCH',
            'rebind',
        );

    } else {

        initialized =
            true;

        debug(
            'SEARCH',
            'initialized',
        );
    }

    basePath =
        form.dataset.basePath
        || '/';

    // =====================================
    // INPUT
    // =====================================

    input.oninput =
        () =>
        {
            clearTimeout(
                debounceTimer,
            );

            debounceTimer =
                window.setTimeout(
                    () =>
                    {
                        void performSearch(
                            input.value.trim(),
                        );
                    },
                    SEARCH_DEBOUNCE,
                );
        };

    // =====================================
    // SUBMIT
    // =====================================

    form.onsubmit =
        (
            event,
        ) =>
        {
            event.preventDefault();

            const value =
                normalizeSearchQuery(
                    input.value.trim(),
                );

            if (!value) {
                return;
            }

            closeDropdown();

            void navigateTo(
                `${basePath}manga/recherche/${encodeURIComponent(value)}`,
            );
        };

    // =====================================
    // CLICK
    // =====================================

    results.onclick =
        (
            event,
        ) =>
        {
            const target =
                event.target;

            if (
                !(
                    target
                    instanceof Element
                )
            ) {
                return;
            }

            const item =
                target.closest(
                    '.search-result-item',
                );

            if (
                !(
                    item
                    instanceof HTMLAnchorElement
                )
            ) {
                return;
            }

            event.preventDefault();

            closeDropdown();

            void navigateTo(
                item.href,
            );
        };

    // =====================================
    // KEYBOARD
    // =====================================

    input.onkeydown =
        handleKeyboard;

    // =====================================
    // OUTSIDE
    // =====================================

    document.addEventListener(
        'click',
        (
            event,
        ) =>
        {
            const target =
                event.target;

            if (
                !(
                    target
                    instanceof Element
                )
            ) {
                return;
            }

            if (
                !target.closest(
                    '.js-header-search',
                )
            ) {

                closeDropdown();
            }
        },
    );

    // =====================================
    // NAVIGATION
    // =====================================

    document.addEventListener(
        'app:navigation-start',
        closeDropdown,
    );
}