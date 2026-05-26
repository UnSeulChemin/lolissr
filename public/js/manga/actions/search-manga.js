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
// Config
// =========================================

const SEARCH_DEBOUNCE =
    250;

const MIN_SEARCH_LENGTH =
    2;

// =========================================
// State
// =========================================

let initialized =
    false;

// =========================================
// Init
// =========================================

export function initSearchManga()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    const form =
        $(
            '.js-header-search',
        );

    const input =
        $(
            '#header-search-input',
        );

    const results =
        $(
            '#header-search-results',
        );

    const dropdown =
        $(
            '.js-header-search-dropdown',
        );

    if (
        !form
        || !input
        || !results
        || !dropdown
    ) {

        debug(
            'SEARCH',
            'header not found',
        );

        return;
    }

    const basePath =
        form.dataset.basePath
        || '/';

    let debounceTimer =
        null;

    let abortController =
        null;

    let activeIndex =
        -1;

    // =====================================
    // Helpers
    // =====================================

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

        const activeItem =
            items[
                activeIndex
            ];

        activeItem?.scrollIntoView({
            block:
                'nearest',
        });
    }

    function openDropdown()
    {
        dropdown.classList.add(
            'has-results',
        );
    }

    function closeDropdown()
    {
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
        isLoading,
    )
    {
        dropdown.classList.toggle(
            'is-loading',
            isLoading,
        );

        if (isLoading) {

            dropdown.classList.remove(
                'has-results',
            );
        }
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

    // =====================================
    // Search
    // =====================================

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

            debug(
                'SEARCH',
                'fetch',
                normalized,
            );

            setLoading(
                true,
            );

            results.innerHTML =
                '';

            resetActive();

            // =============================
            // Shortcuts
            // =============================

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

            // =============================
            // API
            // =============================

            const data =
                await fetchSearchResults(
                    `${basePath}manga/ajax/recherche/${encodeURIComponent(normalized)}?t=${Date.now()}`,
                    currentController.signal,
                );

            const items =
                data.data?.results
                || [];

            if (
                !items.length
                && !shortcuts.length
            ) {

                renderEmptyState();

                return;
            }

            items.forEach(
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
                error instanceof Error
                && error.name
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

    // =====================================
    // Submit
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

            if (
                value === ''
            ) {
                return;
            }

            value =
                normalizeSearchQuery(
                    value,
                );

            if (
                value === ''
            ) {
                return;
            }

            closeDropdown();

            window.location.href =
                `${basePath}manga/recherche/${encodeURIComponent(value)}`;
        },
    );

    // =====================================
    // Input
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
    // Keyboard
    // =====================================

    input.addEventListener(
        'keydown',
        (
            event,
        ) =>
        {
            const items =
                getItems();

            if (
                event.key
                === 'Escape'
            ) {

                event.preventDefault();

                closeDropdown();

                return;
            }

            if (
                !dropdown.classList.contains(
                    'has-results',
                )
                || !items.length
            ) {
                return;
            }

            // DOWN

            if (
                event.key
                === 'ArrowDown'
            ) {

                event.preventDefault();

                activeIndex =
                    activeIndex
                    < items.length - 1
                        ? activeIndex + 1
                        : 0;

                updateActive();

                return;
            }

            // UP

            if (
                event.key
                === 'ArrowUp'
            ) {

                event.preventDefault();

                activeIndex =
                    activeIndex > 0
                        ? activeIndex - 1
                        : items.length - 1;

                updateActive();

                return;
            }

            // ENTER

            if (
                event.key
                === 'Enter'
            ) {

                const item =
                    items[
                        activeIndex
                    ];

                if (!item) {
                    return;
                }

                event.preventDefault();

                window.location.href =
                    item.href;
            }
        },
    );

    // =====================================
    // Mouse Hover
    // =====================================

    results.addEventListener(
        'mouseover',
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

            const items =
                getItems();

            const index =
                items.indexOf(
                    item,
                );

            if (
                index === -1
            ) {
                return;
            }

            activeIndex =
                index;

            updateActive();
        },
    );

    // =====================================
    // Outside Click
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

    debug(
        'SEARCH',
        'initialized',
    );
}