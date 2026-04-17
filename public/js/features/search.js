/**
 * Normalise une valeur de recherche pour l'URL.
 */
function normalizeSearchQuery(value)
{
    return value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');
}

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
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * Met en surbrillance les termes recherchés.
 */
function highlightSearchTerm(text, rawQuery)
{
    const safeText = escapeHtml(text);
    const trimmedQuery = rawQuery.trim();

    if (trimmedQuery === '')
    {
        return safeText;
    }

    const queryParts = trimmedQuery
        .split(/\s+/)
        .filter(Boolean)
        .map(escapeRegExp);

    if (queryParts.length === 0)
    {
        return safeText;
    }

    const regex = new RegExp(`(${queryParts.join('|')})`, 'ig');

    return safeText.replace(
        regex,
        '<mark class="search-highlight">$1</mark>'
    );
}

export function initLiveSearch()
{
    /*
    |------------------------------------------------------------------
    | Sélection des éléments
    |------------------------------------------------------------------
    */

    const searchForm = document.querySelector('.js-header-search');
    const searchInput = document.getElementById('header-search-input');
    const searchResultsBox = document.getElementById('header-search-results');
    const searchDropdown = document.querySelector('.js-header-search-dropdown');

    if (!searchForm || !searchInput || !searchResultsBox || !searchDropdown)
    {
        return;
    }

    /*
    |------------------------------------------------------------------
    | Protection anti double init
    |------------------------------------------------------------------
    */

    if (searchForm.dataset.liveSearchInit === 'true')
    {
        return;
    }

    searchForm.dataset.liveSearchInit = 'true';

    /*
    |------------------------------------------------------------------
    | Configuration
    |------------------------------------------------------------------
    */

    const basePath = searchForm.dataset.basePath || '/';

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
            searchResultsBox.querySelectorAll('.search-result-item')
        );
    }

    function resetActiveSearchResult()
    {
        activeResultIndex = -1;

        getSearchResultItems().forEach((item) =>
        {
            item.classList.remove('is-active');
        });
    }

    function updateActiveSearchResult()
    {
        const items = getSearchResultItems();

        items.forEach((item, index) =>
        {
            item.classList.toggle('is-active', index === activeResultIndex);
        });

        if (activeResultIndex >= 0 && items[activeResultIndex])
        {
            items[activeResultIndex].scrollIntoView({
                block: 'nearest'
            });
        }
    }

    function activateFirstSearchResult()
    {
        const items = getSearchResultItems();

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
        searchDropdown.classList.add('has-results');
    }

    function closeSearchDropdown()
    {
        searchResultsBox.innerHTML = '';
        searchDropdown.classList.remove('is-loading', 'has-results');

        if (searchAbortController)
        {
            searchAbortController.abort();
            searchAbortController = null;
        }

        resetActiveSearchResult();
    }

    function setSearchLoadingState(isLoading)
    {
        searchDropdown.classList.toggle('is-loading', isLoading);

        if (isLoading)
        {
            searchDropdown.classList.remove('has-results');
        }
    }

    function renderSearchEmptyState(message = 'Aucun manga trouvé')
    {
        searchResultsBox.innerHTML = `
            <div class="search-result-empty">
                ${escapeHtml(message)}
            </div>
        `;

        openSearchDropdown();
        resetActiveSearchResult();
    }

    function buildSearchResultItem(manga, rawValue)
    {
        const resultLink = document.createElement('a');

        resultLink.href =
            `${basePath}manga/${encodeURIComponent(manga.slug)}/${manga.numero}`;

        resultLink.className = 'search-result-item';

        resultLink.innerHTML = `
            <img
                src="${basePath}public/images/mangas/thumbnail/${manga.thumbnail}.${manga.extension}"
                alt="${escapeHtml(manga.livre)}">
            <span class="search-result-content">
                <strong class="search-result-title">
                    ${highlightSearchTerm(manga.livre, rawValue)}
                </strong>
                <small class="search-result-meta">
                    Tome ${String(manga.numero).padStart(2, '0')}
                </small>
            </span>
        `;

        resultLink.addEventListener('mouseenter', () =>
        {
            const items = getSearchResultItems();

            activeResultIndex = items.indexOf(resultLink);
            updateActiveSearchResult();
        });

        return resultLink;
    }

    async function fetchLiveSearchResults(rawValue)
    {
        if (searchAbortController)
        {
            searchAbortController.abort();
        }

        const normalizedValue = normalizeSearchQuery(rawValue);

        if (rawValue.length < 2 || normalizedValue === '')
        {
            closeSearchDropdown();
            return;
        }

        searchAbortController = new AbortController();

        try
        {
            setSearchLoadingState(true);
            searchResultsBox.innerHTML = '';
            resetActiveSearchResult();

            const response = await fetch(
                `${basePath}manga/search-ajax/${encodeURIComponent(normalizedValue)}`,
                {
                    signal: searchAbortController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            if (!response.ok)
            {
                throw new Error('Erreur recherche live');
            }

            const data = await response.json();

            searchResultsBox.innerHTML = '';
            resetActiveSearchResult();

            if (!Array.isArray(data) || data.length === 0)
            {
                renderSearchEmptyState();
                return;
            }

            data.forEach((manga) =>
            {
                searchResultsBox.appendChild(
                    buildSearchResultItem(manga, rawValue)
                );
            });

            openSearchDropdown();
            activateFirstSearchResult();
        }
        catch (error)
        {
            if (error.name !== 'AbortError')
            {
                renderSearchEmptyState('Erreur de chargement');
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

    searchForm.addEventListener('submit', (event) =>
    {
        event.preventDefault();

        if (activeResultIndex >= 0)
        {
            const items = getSearchResultItems();

            if (items[activeResultIndex])
            {
                window.location.href = items[activeResultIndex].href;
                return;
            }
        }

        let value = searchInput.value.trim();

        if (value === '')
        {
            return;
        }

        value = normalizeSearchQuery(value);

        if (value === '')
        {
            return;
        }

        window.location.href =
            `${basePath}manga/recherche/${encodeURIComponent(value)}`;
    });

    /*
    |------------------------------------------------------------------
    | Saisie utilisateur
    |------------------------------------------------------------------
    */

    searchInput.addEventListener('input', () =>
    {
        clearTimeout(searchDebounceTimer);

        searchDebounceTimer = setTimeout(() =>
        {
            fetchLiveSearchResults(searchInput.value.trim());
        }, 250);
    });

    /*
    |------------------------------------------------------------------
    | Navigation clavier
    |------------------------------------------------------------------
    */

    searchInput.addEventListener('keydown', (event) =>
    {
        const items = getSearchResultItems();

        if (event.key === 'Escape')
        {
            event.preventDefault();
            closeSearchDropdown();
            return;
        }

        if (!searchDropdown.classList.contains('has-results') || items.length === 0)
        {
            return;
        }

        if (event.key === 'ArrowDown')
        {
            event.preventDefault();

            activeResultIndex = activeResultIndex < items.length - 1
                ? activeResultIndex + 1
                : 0;

            updateActiveSearchResult();
            return;
        }

        if (event.key === 'ArrowUp')
        {
            event.preventDefault();

            activeResultIndex = activeResultIndex > 0
                ? activeResultIndex - 1
                : items.length - 1;

            updateActiveSearchResult();
            return;
        }

        if (event.key === 'Enter' && activeResultIndex >= 0 && items[activeResultIndex])
        {
            event.preventDefault();
            window.location.href = items[activeResultIndex].href;
        }
    });

    /*
    |------------------------------------------------------------------
    | Fermeture clic extérieur
    |------------------------------------------------------------------
    */

    document.addEventListener('click', (event) =>
    {
        const clickedInsideForm = event.target.closest('.js-header-search');
        const clickedInsideDropdown = event.target.closest('.js-header-search-dropdown');

        if (!clickedInsideForm && !clickedInsideDropdown)
        {
            closeSearchDropdown();
        }
    });
}