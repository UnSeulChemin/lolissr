import { normalizeSearchQuery } from '../utils/slug.js';
import { findSearchShortcuts } from '../../navigation/search-shortcuts.js';

/* ======================================================
   Utils
====================================================== */

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeRegExp(value) {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function highlightSearchTerm(text, rawQuery) {
    const safeText = escapeHtml(text);
    const trimmedQuery = rawQuery.trim();

    if (trimmedQuery === '') return safeText;

    const queryParts = trimmedQuery
        .split(/\s+/)
        .filter(Boolean)
        .map(escapeRegExp);

    if (queryParts.length === 0) return safeText;

    const regex = new RegExp(`(${queryParts.join('|')})`, 'ig');

    return safeText.replace(regex, `<mark class="search-highlight">$1</mark>`);
}

/* ======================================================
   Main Init
====================================================== */

export function initSearchManga() {
    const mangaSearchForm = document.querySelector('.js-header-search');
    const mangaSearchInput = document.getElementById('header-search-input');
    const mangaSearchResults = document.getElementById('header-search-results');
    const mangaSearchDropdown = document.querySelector('.js-header-search-dropdown');

    // Check DOM
    if (!mangaSearchForm || !mangaSearchInput || !mangaSearchResults || !mangaSearchDropdown) {
        console.warn('Search init aborted: elements not found');
        return;
    }

    // Prevent double init
    if (mangaSearchForm.dataset.searchMangaInit === 'true') return;
    mangaSearchForm.dataset.searchMangaInit = 'true';

    const basePath = mangaSearchForm.dataset.basePath || '/';
    let searchDebounceTimer = null;
    let searchAbortController = null;
    let activeResultIndex = -1;

    /* --------------------------
       Helpers
    -------------------------- */

    function getSearchResultItems() {
        return Array.from(mangaSearchResults.querySelectorAll('.search-result-item'));
    }

    function resetActiveSearchResult() {
        activeResultIndex = -1;
        getSearchResultItems().forEach(item => item.classList.remove('is-active'));
    }

    function updateActiveSearchResult() {
        const items = getSearchResultItems();
        items.forEach((item, index) => item.classList.toggle('is-active', index === activeResultIndex));
        const activeItem = items[activeResultIndex];
        if (activeItem) activeItem.scrollIntoView({ block: 'nearest' });
    }

    function activateFirstSearchResult() {
        const items = getSearchResultItems();
        if (!items.length) { activeResultIndex = -1; return; }
        activeResultIndex = 0;
        updateActiveSearchResult();
    }

    function openSearchDropdown() {
        mangaSearchDropdown.classList.add('has-results');
    }

    function closeSearchDropdown() {
        mangaSearchResults.innerHTML = '';
        mangaSearchDropdown.classList.remove('is-loading', 'has-results');
        if (searchAbortController) {
            searchAbortController.abort();
            searchAbortController = null;
        }
        resetActiveSearchResult();
    }

    function setSearchLoadingState(isLoading) {
        mangaSearchDropdown.classList.toggle('is-loading', isLoading);
        if (isLoading) mangaSearchDropdown.classList.remove('has-results');
    }

    function renderSearchEmptyState(message = 'Aucun résultat trouvé') {
        mangaSearchResults.innerHTML = `<div class="search-result-empty">${escapeHtml(message)}</div>`;
        openSearchDropdown();
        resetActiveSearchResult();
    }

    /* --------------------------
       Build Results
    -------------------------- */

    function buildMangaSearchResult(manga, rawValue) {
        const resultLink = document.createElement('a');
        resultLink.href = `${basePath}manga/series/${encodeURIComponent(manga.slug)}/${manga.numero}`;
        resultLink.className = 'search-result-item';
        resultLink.innerHTML = `
            <img src="${basePath}images/mangas/thumbnail/${manga.thumbnail}.${manga.extension}" alt="${escapeHtml(manga.livre)}">
            <span class="search-result-content">
                <strong class="search-result-title">${highlightSearchTerm(manga.livre, rawValue)}</strong>
                <small class="search-result-meta">Tome ${String(manga.numero).padStart(2,'0')}</small>
            </span>
        `;
        return resultLink;
    }

    function buildShortcutSearchResult(shortcut) {
        const resultLink = document.createElement('a');
        resultLink.href = `${basePath}${shortcut.url}`;
        resultLink.className = 'search-result-item';
        resultLink.innerHTML = `
            <span class="search-result-icon">${escapeHtml(shortcut.symbol)}</span>
            <span class="search-result-content">
                <strong class="search-result-title">${escapeHtml(shortcut.title)}</strong>
                <small class="search-result-meta">${escapeHtml(shortcut.description)}</small>
            </span>
        `;
        return resultLink;
    }

    /* --------------------------
       Fetch Results
    -------------------------- */

    async function fetchSearchResults(rawValue) {
        if (searchAbortController) searchAbortController.abort();

        const normalizedValue = normalizeSearchQuery(rawValue);
        if (rawValue.length < 2 || normalizedValue === '') {
            closeSearchDropdown();
            return;
        }

        searchAbortController = new AbortController();

        try {
            setSearchLoadingState(true);
            mangaSearchResults.innerHTML = '';
            resetActiveSearchResult();

            // Shortcuts
            const shortcuts = findSearchShortcuts(rawValue) || [];
            shortcuts.forEach(shortcut => mangaSearchResults.appendChild(buildShortcutSearchResult(shortcut)));

            // AJAX
            const response = await fetch(`${basePath}manga/ajax/recherche/${encodeURIComponent(normalizedValue)}?t=${Date.now()}`, {
                signal: searchAbortController.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) throw new Error('Erreur recherche live');
            const responseData = await response.json();
            const results = responseData.data?.results ?? [];

            if ((!results.length) && (!shortcuts.length)) {
                renderSearchEmptyState();
                return;
            }

            results.forEach(manga => mangaSearchResults.appendChild(buildMangaSearchResult(manga, rawValue)));

            openSearchDropdown();
            activateFirstSearchResult();

        } catch (error) {
            if (error.name !== 'AbortError') {
                renderSearchEmptyState('Erreur de chargement');
                console.error(error);
            }
        } finally {
            setSearchLoadingState(false);
        }
    }

    /* --------------------------
       Event Listeners
    -------------------------- */

    mangaSearchForm.addEventListener('submit', event => {
        event.preventDefault();
        let value = mangaSearchInput.value.trim();
        if (value === '') return;
        value = normalizeSearchQuery(value);
        if (value === '') return;
        closeSearchDropdown();
        window.location.href = `${basePath}manga/recherche/${encodeURIComponent(value)}`;
    });

    mangaSearchInput.addEventListener('input', () => {
        clearTimeout(searchDebounceTimer);
        resetActiveSearchResult();
        searchDebounceTimer = setTimeout(() => fetchSearchResults(mangaSearchInput.value.trim()), 250);
    });

    mangaSearchInput.addEventListener('keydown', event => {
        const items = getSearchResultItems();
        if (event.key === 'Escape') { event.preventDefault(); closeSearchDropdown(); return; }
        if (!mangaSearchDropdown.classList.contains('has-results') || !items.length) return;

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeResultIndex = (activeResultIndex < items.length - 1) ? activeResultIndex + 1 : 0;
            updateActiveSearchResult();
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeResultIndex = (activeResultIndex > 0) ? activeResultIndex - 1 : items.length - 1;
            updateActiveSearchResult();
            return;
        }

        if (event.key === 'Enter') {
            const activeItem = items[activeResultIndex];
            if (!activeItem) return;
            event.preventDefault();
            window.location.href = activeItem.href;
        }
    });

    document.addEventListener('click', event => {
        const clickedInsideForm = event.target.closest('.js-header-search');
        const clickedInsideDropdown = event.target.closest('.js-header-search-dropdown');
        if (!clickedInsideForm && !clickedInsideDropdown) closeSearchDropdown();
    });
}

// Auto-init after DOM ready
document.addEventListener('DOMContentLoaded', () => initSearchManga());