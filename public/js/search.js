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

    let debounceTimer;
    let currentController = null;

    function closeDropdown()
    {
        resultsBox.innerHTML = '';
        dropdown.classList.remove('is-loading', 'has-results');
    }

    function renderEmptyState()
    {
        resultsBox.innerHTML = `
            <div class="search-result-empty">
                Aucun manga trouvé
            </div>
        `;

        dropdown.classList.add('has-results');
    }

    form.addEventListener('submit', (event) =>
    {
        event.preventDefault();

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

        debounceTimer = setTimeout(async () =>
        {
            let value = input.value.trim();

            if (currentController)
            {
                currentController.abort();
            }

            if (value.length < 2)
            {
                closeDropdown();
                return;
            }

            value = normalizeSearchValue(value);

            if (value === '')
            {
                closeDropdown();
                return;
            }

            currentController = new AbortController();

            try
            {
                dropdown.classList.remove('has-results');
                dropdown.classList.add('is-loading');

                const response = await fetch(
                    `${basePath}manga/search-ajax/${encodeURIComponent(value)}`,
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

                if (!Array.isArray(data) || data.length === 0)
                {
                    renderEmptyState();
                    return;
                }

                data.forEach((manga) =>
                {
                    const link = document.createElement('a');

                    link.href =
                        `${basePath}manga/${encodeURIComponent(manga.slug)}/${manga.numero}`;

                    link.className = 'search-result-item';

                    link.innerHTML = `
                        <img
                            src="${basePath}public/images/mangas/thumbnail/${manga.thumbnail}.${manga.extension}"
                            alt="${manga.livre}">
                        <span>
                            ${manga.livre}
                            <small>Tome ${String(manga.numero).padStart(2, '0')}</small>
                        </span>
                    `;

                    resultsBox.appendChild(link);
                });

                dropdown.classList.add('has-results');
            }
            catch (error)
            {
                if (error.name !== 'AbortError')
                {
                    resultsBox.innerHTML = `
                        <div class="search-result-empty">
                            Erreur de chargement
                        </div>
                    `;

                    dropdown.classList.add('has-results');
                }
            }
            finally
            {
                dropdown.classList.remove('is-loading');
            }
        }, 250);
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