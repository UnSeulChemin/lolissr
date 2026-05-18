import { normalizeSearchQuery }
    from '../utils/slug.js';

import { findSearchShortcuts }
    from '../../navigation/search-shortcuts.js';

/**
 * Échappe le HTML.
 */
function escapeHtml(value)
{
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

/**
 * Échappe une chaîne pour RegExp.
 */
function escapeRegExp(value)
{
    return value.replace(
        /[.*+?^${}()|[\]\\]/g,
        '\\$&'
    );
}

/**
 * Met en surbrillance les termes recherchés.
 */
function highlightSearchTerm(
    text,
    rawQuery
)
{
    const safeText =
        escapeHtml(text);

    const trimmedQuery =
        rawQuery.trim();

    if (trimmedQuery === '')
    {
        return safeText;
    }

    const queryParts =
        trimmedQuery
            .split(/\s+/)
            .filter(Boolean)
            .map(escapeRegExp);

    if (queryParts.length === 0)
    {
        return safeText;
    }

    const regex = new RegExp(
        `(${queryParts.join('|')})`,
        'ig'
    );

    return safeText.replace(
        regex,
        '<mark class="search-highlight">$1</mark>'
    );
}

export function initSearchManga()
{
    /*
    |------------------------------------------------------------------
    | Sélection des éléments
    |------------------------------------------------------------------
    */

    const mangaSearchForm =
        document.querySelector(
            '.js-header-search'
        );

    const mangaSearchInput =
        document.getElementById(
            'header-search-input'
        );

    const mangaSearchResults =
        document.getElementById(
            'header-search-results'
        );

    const mangaSearchDropdown =
        document.querySelector(
            '.js-header-search-dropdown'
        );

    if (
        !mangaSearchForm
        || !mangaSearchInput
        || !mangaSearchResults
        || !mangaSearchDropdown
    )
    {
        return;
    }

    /*
    |------------------------------------------------------------------
    | Protection anti double init
    |------------------------------------------------------------------
    */

    if (
        mangaSearchForm.dataset
            .searchMangaInit === 'true'
    )
    {
        return;
    }

    mangaSearchForm.dataset
        .searchMangaInit = 'true';

    /*
    |------------------------------------------------------------------
    | Configuration
    |------------------------------------------------------------------
    */

    const basePath =
        mangaSearchForm.dataset.basePath
        || '/';

    /*
    |------------------------------------------------------------------
    | État
    |------------------------------------------------------------------
    */

    let searchDebounceTimer = null;

    let searchAbortController = null;

    let activeResultIndex = -1;

    function getSearchResultItems()
    {
        return Array.from(
            mangaSearchResults.querySelectorAll(
                '.search-result-item'
            )
        );
    }

    function resetActiveSearchResult()
    {
        activeResultIndex = -1;

        getSearchResultItems()
            .forEach((item) =>
        {
            item.classList.remove(
                'is-active'
            );
        });
    }

    function updateActiveSearchResult()
    {
        const items =
            getSearchResultItems();

        items.forEach(
            (item, index) =>
        {
            item.classList.toggle(
                'is-active',
                index === activeResultIndex
            );
        });

        if (
            activeResultIndex >= 0
            && items[activeResultIndex]
        )
        {
            items[
                activeResultIndex
            ].scrollIntoView({
                block: 'nearest'
            });
        }
    }

    function activateFirstSearchResult()
    {
        const items =
            getSearchResultItems();

        if (items.length === 0)
        {
            activeResultIndex = -1;

            return;
        }

        activeResultIndex = 0;

        updateActiveSearchResult();
    }

    function openSearchDropdown()
    {
        mangaSearchDropdown.classList.add(
            'has-results'
        );
    }

    function closeSearchDropdown()
    {
        mangaSearchResults.innerHTML = '';

        mangaSearchDropdown.classList.remove(
            'is-loading',
            'has-results'
        );

        if (searchAbortController)
        {
            searchAbortController.abort();

            searchAbortController = null;
        }

        resetActiveSearchResult();
    }

    function setSearchLoadingState(
        isLoading
    )
    {
        mangaSearchDropdown.classList.toggle(
            'is-loading',
            isLoading
        );

        if (isLoading)
        {
            mangaSearchDropdown.classList.remove(
                'has-results'
            );
        }
    }

    function renderSearchEmptyState(
        message = 'Aucun résultat trouvé'
    )
    {
        mangaSearchResults.innerHTML = `
            <div class="search-result-empty">
                ${escapeHtml(message)}
            </div>
        `;

        openSearchDropdown();

        resetActiveSearchResult();
    }

    function buildMangaSearchResult(
        manga,
        rawValue
    )
    {
        const resultLink =
            document.createElement('a');

        resultLink.href =
            `${basePath}manga/series/${encodeURIComponent(manga.slug)}/${manga.numero}`;

        resultLink.className =
            'search-result-item';

        resultLink.innerHTML = `
            <img
                src="${basePath}public/images/mangas/thumbnail/${manga.thumbnail}.${manga.extension}"
                alt="${escapeHtml(manga.livre)}">

            <span class="search-result-content">
                <strong class="search-result-title">
                    ${highlightSearchTerm(
                        manga.livre,
                        rawValue
                    )}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(manga.numero).padStart(2, '0')}
                </small>
            </span>
        `;

        resultLink.addEventListener(
            'mouseenter',
            () =>
        {
            const items =
                getSearchResultItems();

            activeResultIndex =
                items.indexOf(resultLink);

            updateActiveSearchResult();
        });

        return resultLink;
    }

    function buildShortcutSearchResult(
        shortcut
    )
    {
        const resultLink =
            document.createElement('a');

        resultLink.href =
            `${basePath}${shortcut.url}`;

        resultLink.className =
            'search-result-item';

        resultLink.innerHTML = `
            <span class="search-result-icon">
                ${escapeHtml(shortcut.symbol)}
            </span>

            <span class="search-result-content">
                <strong class="search-result-title">
                    ${escapeHtml(shortcut.title)}
                </strong>

                <small class="search-result-meta">
                    ${escapeHtml(shortcut.description)}
                </small>
            </span>
        `;

        resultLink.addEventListener(
            'mouseenter',
            () =>
        {
            const items =
                getSearchResultItems();

            activeResultIndex =
                items.indexOf(resultLink);

            updateActiveSearchResult();
        });

        return resultLink;
    }

    async function fetchSearchResults(
        rawValue
    )
    {
        if (searchAbortController)
        {
            searchAbortController.abort();
        }

        const normalizedValue =
            normalizeSearchQuery(
                rawValue
            );

        if (
            rawValue.length < 2
            || normalizedValue === ''
        )
        {
            closeSearchDropdown();

            return;
        }

        searchAbortController =
            new AbortController();

        try
        {
            setSearchLoadingState(true);

            mangaSearchResults.innerHTML = '';

            resetActiveSearchResult();

            const shortcuts =
                findSearchShortcuts(rawValue);

            shortcuts.forEach((shortcut) =>
            {
                mangaSearchResults.appendChild(
                    buildShortcutSearchResult(
                        shortcut
                    )
                );
            });

            const response =
                await fetch(
                `${basePath}manga/ajax/search/${encodeURIComponent(normalizedValue)}`,
                {
                    signal:
                        searchAbortController.signal,

                    headers:
                    {
                        'X-Requested-With':
                            'XMLHttpRequest'
                    }
                }
            );

            if (!response.ok)
            {
                throw new Error(
                    'Erreur recherche live'
                );
            }

            const data =
                await response.json();

            if (
                (!Array.isArray(data)
                || data.length === 0)
                && shortcuts.length === 0
            )
            {
                renderSearchEmptyState();

                return;
            }

            data.forEach((manga) =>
            {
                mangaSearchResults.appendChild(
                    buildMangaSearchResult(
                        manga,
                        rawValue
                    )
                );
            });

            openSearchDropdown();

            activateFirstSearchResult();
        }
        catch (error)
        {
            if (
                error.name
                !== 'AbortError'
            )
            {
                renderSearchEmptyState(
                    'Erreur de chargement'
                );
            }
        }
        finally
        {
            setSearchLoadingState(false);
        }
    }

    /*
    |------------------------------------------------------------------
    | Soumission formulaire
    |------------------------------------------------------------------
    */

    mangaSearchForm.addEventListener(
        'submit',
        (event) =>
        {
            event.preventDefault();

            if (activeResultIndex >= 0)
            {
                const items =
                    getSearchResultItems();

                if (
                    items[
                        activeResultIndex
                    ]
                )
                {
                    window.location.href =
                        items[
                            activeResultIndex
                        ].href;

                    return;
                }
            }

            let value =
                mangaSearchInput.value.trim();

            if (value === '')
            {
                return;
            }

            value =
                normalizeSearchQuery(
                    value
                );

            if (value === '')
            {
                return;
            }

            window.location.href =
                `${basePath}manga/recherche/${encodeURIComponent(value)}`;
        }
    );

    mangaSearchInput.addEventListener(
        'input',
        () =>
        {
            clearTimeout(
                searchDebounceTimer
            );

            searchDebounceTimer =
                setTimeout(() =>
            {
                fetchSearchResults(
                    mangaSearchInput.value.trim()
                );
            }, 250);
        }
    );

    mangaSearchInput.addEventListener(
        'keydown',
        (event) =>
        {
            const items =
                getSearchResultItems();

            if (event.key === 'Escape')
            {
                event.preventDefault();

                closeSearchDropdown();

                return;
            }

            if (
                !mangaSearchDropdown
                    .classList.contains(
                        'has-results'
                    )
                || items.length === 0
            )
            {
                return;
            }

            if (event.key === 'ArrowDown')
            {
                event.preventDefault();

                activeResultIndex =
                    activeResultIndex
                        < items.length - 1
                            ? activeResultIndex + 1
                            : 0;

                updateActiveSearchResult();

                return;
            }

            if (event.key === 'ArrowUp')
            {
                event.preventDefault();

                activeResultIndex =
                    activeResultIndex > 0
                        ? activeResultIndex - 1
                        : items.length - 1;

                updateActiveSearchResult();

                return;
            }

            if (
                event.key === 'Enter'
                && activeResultIndex >= 0
                && items[
                    activeResultIndex
                ]
            )
            {
                event.preventDefault();

                window.location.href =
                    items[
                        activeResultIndex
                    ].href;
            }
        }
    );

    document.addEventListener(
        'click',
        (event) =>
        {
            const clickedInsideForm =
                event.target.closest(
                    '.js-header-search'
                );

            const clickedInsideDropdown =
                event.target.closest(
                    '.js-header-search-dropdown'
                );

            if (
                !clickedInsideForm
                && !clickedInsideDropdown
            )
            {
                closeSearchDropdown();
            }
        }
    );
}