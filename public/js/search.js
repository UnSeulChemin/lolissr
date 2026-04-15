function normalizeSearchValue(value)
{
    return value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s\-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/\-+/g, '-')
        .replace(/^\-+|\-+$/g, '');
}

function escapeHtml(value)
{
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeRegExp(value)
{
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function highlightTerm(text, rawQuery)
{
    const safeText = escapeHtml(text);
    const query = rawQuery.trim();

    if (query === '')
    {
        return safeText;
    }

    const parts = query
        .split(/\s+/)
        .filter(Boolean)
        .map(escapeRegExp);

    if (parts.length === 0)
    {
        return safeText;
    }

    const regex = new RegExp(`(${parts.join('|')})`, 'ig');

    return safeText.replace(
        regex,
        '<mark class="search-highlight">$1</mark>'
    );
}

export function initLiveSearch()
{
    const form = document.querySelector('.js-header-search');
    const input = document.getElementById('header-search-input');
    const resultsBox = document.getElementById('header-search-results');
    const dropdown = document.querySelector('.js-header-search-dropdown');

    if (!form || !input || !resultsBox || !dropdown)
    {
        return;
    }

    const basePath = form.dataset.basePath || '/';

    let debounceTimer = null;
    let currentController = null;
    let activeIndex = -1;

    function getItems()
    {
        return Array.from(
            resultsBox.querySelectorAll('.search-result-item')
        );
    }

    function resetActiveItem()
    {
        activeIndex = -1;

        getItems().forEach((item) =>
        {
            item.classList.remove('is-active');
        });
    }

    function updateActiveItem()
    {
        const items = getItems();

        items.forEach((item, index) =>
        {
            item.classList.toggle('is-active', index === activeIndex);
        });

        if (activeIndex >= 0 && items[activeIndex])
        {
            items[activeIndex].scrollIntoView({
                block: 'nearest'
            });
        }
    }

    function openDropdown()
    {
        dropdown.classList.add('has-results');
    }

    function closeDropdown()
    {
        resultsBox.innerHTML = '';
        dropdown.classList.remove('is-loading', 'has-results');
        resetActiveItem();
    }

    function setLoading(isLoading)
    {
        dropdown.classList.toggle('is-loading', isLoading);

        if (isLoading)
        {
            dropdown.classList.remove('has-results');
        }
    }

    function renderEmptyState(message = 'Aucun manga trouvé')
    {
        resultsBox.innerHTML = `
            <div class="search-result-empty">
                ${escapeHtml(message)}
            </div>
        `;

        openDropdown();
        resetActiveItem();
    }

    function buildResultItem(manga, rawValue)
    {
        const link = document.createElement('a');

        link.href =
            `${basePath}manga/${encodeURIComponent(manga.slug)}/${manga.numero}`;

        link.className = 'search-result-item';

        link.innerHTML = `
            <img
                src="${basePath}public/images/mangas/thumbnail/${manga.thumbnail}.${manga.extension}"
                alt="${escapeHtml(manga.livre)}">
            <span class="search-result-content">
                <strong class="search-result-title">
                    ${highlightTerm(manga.livre, rawValue)}
                </strong>
                <small class="search-result-meta">
                    Tome ${String(manga.numero).padStart(2, '0')}
                </small>
            </span>
        `;

        link.addEventListener('mouseenter', () =>
        {
            const items = getItems();
            activeIndex = items.indexOf(link);
            updateActiveItem();
        });

        return link;
    }

    async function fetchResults(rawValue)
    {
        if (currentController)
        {
            currentController.abort();
        }

        const normalizedValue = normalizeSearchValue(rawValue);

        if (rawValue.length < 2 || normalizedValue === '')
        {
            closeDropdown();
            return;
        }

        currentController = new AbortController();

        try
        {
            setLoading(true);
            resultsBox.innerHTML = '';
            resetActiveItem();

            const response = await fetch(
                `${basePath}manga/search-ajax/${encodeURIComponent(normalizedValue)}`,
                {
                    signal: currentController.signal,
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

            resultsBox.innerHTML = '';
            resetActiveItem();

            if (!Array.isArray(data) || data.length === 0)
            {
                renderEmptyState();
                return;
            }

            data.forEach((manga) =>
            {
                resultsBox.appendChild(buildResultItem(manga, rawValue));
            });

            openDropdown();
        }
        catch (error)
        {
            if (error.name !== 'AbortError')
            {
                renderEmptyState('Erreur de chargement');
            }
        }
        finally
        {
            setLoading(false);
        }
    }

    form.addEventListener('submit', (event) =>
    {
        event.preventDefault();

        if (activeIndex >= 0)
        {
            const items = getItems();

            if (items[activeIndex])
            {
                window.location.href = items[activeIndex].href;
                return;
            }
        }

        let value = input.value.trim();

        if (value === '')
        {
            return;
        }

        value = normalizeSearchValue(value);

        if (value === '')
        {
            return;
        }

        window.location.href = `${basePath}manga/recherche/${encodeURIComponent(value)}`;
    });

    input.addEventListener('input', () =>
    {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() =>
        {
            fetchResults(input.value.trim());
        }, 250);
    });

    input.addEventListener('keydown', (event) =>
    {
        const items = getItems();

        if (event.key === 'Escape')
        {
            event.preventDefault();
            closeDropdown();
            return;
        }

        if (!dropdown.classList.contains('has-results') || items.length === 0)
        {
            return;
        }

        if (event.key === 'ArrowDown')
        {
            event.preventDefault();

            activeIndex = activeIndex < items.length - 1
                ? activeIndex + 1
                : 0;

            updateActiveItem();
            return;
        }

        if (event.key === 'ArrowUp')
        {
            event.preventDefault();

            activeIndex = activeIndex > 0
                ? activeIndex - 1
                : items.length - 1;

            updateActiveItem();
            return;
        }

        if (event.key === 'Enter' && activeIndex >= 0 && items[activeIndex])
        {
            event.preventDefault();
            window.location.href = items[activeIndex].href;
        }
    });

    document.addEventListener('click', (event) =>
    {
        const clickedInsideForm = event.target.closest('.js-header-search');
        const clickedInsideDropdown = event.target.closest('.js-header-search-dropdown');

        if (!clickedInsideForm && !clickedInsideDropdown)
        {
            closeDropdown();
        }
    });
}