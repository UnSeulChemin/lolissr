// ======================================================
// Search Manga
// ======================================================

import {
    normalizeSearchQuery,
} from '../utils/slug.js';

import {
    findSearchShortcuts,
} from '../../core/keyboard/search-shortcuts.js';

import {
    debug,
    debugError,
} from '../../core/debug.js';

// ======================================================
// Config
// ======================================================

const SEARCH_DEBOUNCE =
    250;

const MIN_SEARCH_LENGTH =
    2;

// ======================================================
// State
// ======================================================

let initialized =
    false;

// ======================================================
// Utils
// ======================================================

function escapeHtml(
    value,
)
{
    return String(
        value,
    )
        .replaceAll(
            '&',
            '&amp;',
        )
        .replaceAll(
            '<',
            '&lt;',
        )
        .replaceAll(
            '>',
            '&gt;',
        )
        .replaceAll(
            '"',
            '&quot;',
        )
        .replaceAll(
            "'",
            '&#039;',
        );
}

function escapeRegExp(
    value,
)
{
    return value.replace(
        /[.*+?^${}()|[\]\\]/g,
        '\\$&',
    );
}

function highlightSearchTerm(
    text,
    rawQuery,
)
{
    const safeText =
        escapeHtml(
            text,
        );

    const trimmedQuery =
        rawQuery.trim();

    if (
        trimmedQuery === ''
    ) {
        return safeText;
    }

    const queryParts =
        trimmedQuery
            .split(
                /\s+/,
            )
            .filter(
                Boolean,
            )
            .map(
                escapeRegExp,
            );

    if (
        !queryParts.length
    ) {
        return safeText;
    }

    const regex =
        new RegExp(
            `(${queryParts.join('|')})`,
            'ig',
        );

    return safeText.replace(
        regex,
        '<mark class="search-highlight">$1</mark>',
    );
}

// ======================================================
// Init
// ======================================================

export function initSearchManga()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    const form =
        document.querySelector(
            '.js-header-search',
        );

    const input =
        document.getElementById(
            'header-search-input',
        );

    const results =
        document.getElementById(
            'header-search-results',
        );

    const dropdown =
        document.querySelector(
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

    // ==================================================
    // Helpers
    // ==================================================

    function getItems()
    {
        return Array.from(
            results.querySelectorAll(
                '.search-result-item',
            ),
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

        if (activeItem) {

            activeItem.scrollIntoView({
                block:
                    'nearest',
            });
        }
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

        if (
            abortController
        ) {

            abortController.abort();

            abortController =
                null;
        }

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
                ${escapeHtml(message)}
            </div>
        `;

        openDropdown();

        resetActive();
    }

    // ==================================================
    // Builders
    // ==================================================

    function buildMangaSearchResult(
        manga,
        rawValue,
    )
    {
        const item =
            document.createElement(
                'a',
            );

        item.href =
            `${basePath}manga/series/${encodeURIComponent(manga.slug)}/${manga.numero}`;

        item.className =
            'search-result-item';

        item.innerHTML =
        `
            <img
                src="${basePath}images/mangas/thumbnail/${manga.thumbnail}.${manga.extension}"
                alt="${escapeHtml(manga.livre)}"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${highlightSearchTerm(
                        manga.livre,
                        rawValue,
                    )}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(
                        manga.numero,
                    ).padStart(
                        2,
                        '0',
                    )}
                </small>

            </span>
        `;

        return item;
    }

    function buildShortcutSearchResult(
        shortcut,
    )
    {
        const item =
            document.createElement(
                'a',
            );

        item.href =
            `${basePath}${shortcut.url}`;

        item.className =
            'search-result-item';

        item.innerHTML =
        `
            <span class="search-result-icon">
                ${escapeHtml(
                    shortcut.symbol,
                )}
            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${escapeHtml(
                        shortcut.title,
                    )}
                </strong>

                <small class="search-result-meta">
                    ${escapeHtml(
                        shortcut.description,
                    )}
                </small>

            </span>
        `;

        return item;
    }

    // ==================================================
    // Fetch
    // ==================================================

    async function fetchSearchResults(
        rawValue,
    )
    {
        if (
            abortController
        ) {

            abortController.abort();
        }

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

            // ======================================
            // Shortcuts
            // ======================================

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
                        ),
                    );
                },
            );

            // ======================================
            // Fetch API
            // ======================================

            const response =
                await fetch(
                    `${basePath}manga/ajax/recherche/${encodeURIComponent(normalized)}?t=${Date.now()}`,
                    {
                        signal:
                            currentController.signal,

                        credentials:
                            'same-origin',

                        headers:
                        {
                            'X-Requested-With':
                                'XMLHttpRequest',

                            'X-Partial':
                                'true',

                            'Accept':
                                'application/json',
                        },
                    },
                );

            if (
                !response.ok
            ) {

                throw new Error(
                    'Erreur recherche live',
                );
            }

            const data =
                await response.json();

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

    // ==================================================
    // Submit
    // ==================================================

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

    // ==================================================
    // Input
    // ==================================================

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
                        fetchSearchResults(
                            input.value.trim(),
                        );
                    },
                    SEARCH_DEBOUNCE,
                );
        },
    );

    // ==================================================
    // Keyboard
    // ==================================================

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

            // ======================================
            // DOWN
            // ======================================

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

            // ======================================
            // UP
            // ======================================

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

            // ======================================
            // ENTER
            // ======================================

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

    // ==================================================
    // Mouse Hover
    // ==================================================

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

    // ==================================================
    // Outside Click
    // ==================================================

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

            const clickedInside =
                target.closest(
                    '.js-header-search',
                );

            if (
                !clickedInside
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