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
    if (!results || !dropdown || !input) {
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

    activeIndex =
        -1;

    resetActive();
}

function setLoading(
    isLoading,
)
{
    dropdown?.classList.toggle(
        'is-loading',
        isLoading,
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

        const shortcuts =
            findSearchShortcuts(
                rawValue,
            ) || [];

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
// INIT
// =========================================

export function initSearchManga()
{
    if (initialized) {
        return;
    }

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

    initialized =
        true;

    basePath =
        form.dataset.basePath
        || '/';

    // =====================================
    // SUBMIT
    // =====================================

    form.addEventListener(
        'submit',
        (
            event,
        ) =>
        {
            event.preventDefault();

            let value =
                input.value.trim();

            if (!value) {
                return;
            }

            value =
                normalizeSearchQuery(
                    value,
                );

            if (!value) {
                return;
            }

            window.location.href =
                `${basePath}manga/recherche/${encodeURIComponent(value)}`;
        },
    );

    // =====================================
    // INPUT
    // =====================================

    input.addEventListener(
        'input',
        () =>
        {
            clearTimeout(
                debounceTimer,
            );

            resetActive();

            debounceTimer =
                window.setTimeout(
                    () =>
                    {
                        performSearch(
                            input.value.trim(),
                        );
                    },
                    SEARCH_DEBOUNCE,
                );
        },
    );

    // =====================================
    // OUTSIDE CLICK
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
                !(target instanceof Element)
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

    results.addEventListener(
        'click',
        (
            event,
        ) =>
        {
            const target =
                event.target;

            if (
                !(target instanceof Element)
            ) {
                return;
            }

            const item =
                target.closest(
                    '.search-result-item',
                );

            if (!item) {
                return;
            }

            closeDropdown();
        },
    );

    document.addEventListener(
        'app:navigation-start',
        closeDropdown,
    );

    debug(
        'SEARCH',
        'initialized',
    );
}