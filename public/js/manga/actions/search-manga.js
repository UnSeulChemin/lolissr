// ======================================================
// search-manga.js
// ======================================================
import { normalizeSearchQuery } from '../utils/slug.js';
import { findSearchShortcuts } from '../../navigation/search-shortcuts.js';

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
    const queryParts = trimmedQuery.split(/\s+/).filter(Boolean).map(escapeRegExp);
    if (queryParts.length === 0) return safeText;
    const regex = new RegExp(`(${queryParts.join('|')})`, 'ig');
    return safeText.replace(regex, `<mark class="search-highlight">$1</mark>`);
}

export function initSearchManga() {
    const form = document.querySelector('.js-header-search');
    const input = document.getElementById('header-search-input');
    const results = document.getElementById('header-search-results');
    const dropdown = document.querySelector('.js-header-search-dropdown');

    if (!form || !input || !results || !dropdown) return;
    if (form.dataset.searchMangaInit === 'true') return;
    form.dataset.searchMangaInit = 'true';

    const basePath = form.dataset.basePath || '/';
    let debounceTimer = null;
    let abortController = null;
    let activeIndex = -1;

    const getItems = () => Array.from(results.querySelectorAll('.search-result-item'));
    const resetActive = () => { activeIndex = -1; getItems().forEach(i => i.classList.remove('is-active')); };
    const updateActive = () => {
        const items = getItems();
        items.forEach((i, idx) => i.classList.toggle('is-active', idx === activeIndex));
        if (items[activeIndex]) items[activeIndex].scrollIntoView({block:'nearest'});
    };
    const openDropdown = () => dropdown.classList.add('has-results');
    const closeDropdown = () => {
        results.innerHTML='';
        dropdown.classList.remove('is-loading','has-results');
        if (abortController){ abortController.abort(); abortController=null; }
        resetActive();
    };
    const setLoading = isLoading => { dropdown.classList.toggle('is-loading', isLoading); if(isLoading) dropdown.classList.remove('has-results'); };
    const emptyState = (msg='Aucun résultat trouvé') => { results.innerHTML=`<div class="search-result-empty">${msg}</div>`; openDropdown(); resetActive(); };

    const buildMangaSearchResult = (manga, rawValue) => {
        const a = document.createElement('a');
        a.href = `${basePath}manga/series/${encodeURIComponent(manga.slug)}/${manga.numero}`;
        a.className = 'search-result-item';
        a.innerHTML = `<img src="${basePath}images/mangas/thumbnail/${manga.thumbnail}.${manga.extension}" alt="${escapeHtml(manga.livre)}">
        <span class="search-result-content">
            <strong class="search-result-title">${highlightSearchTerm(manga.livre, rawValue)}</strong>
            <small class="search-result-meta">Tome ${String(manga.numero).padStart(2,'0')}</small>
        </span>`;
        return a;
    };

    const buildShortcutSearchResult = (shortcut) => {
        const a = document.createElement('a');
        a.href = `${basePath}${shortcut.url}`;
        a.className = 'search-result-item';
        a.innerHTML = `<span class="search-result-icon">${escapeHtml(shortcut.symbol)}</span>
        <span class="search-result-content">
            <strong class="search-result-title">${escapeHtml(shortcut.title)}</strong>
            <small class="search-result-meta">${escapeHtml(shortcut.description)}</small>
        </span>`;
        return a;
    };

    const fetchSearchResults = async (rawValue) => {
        if (abortController) abortController.abort();
        const norm = normalizeSearchQuery(rawValue);
        if (rawValue.length < 2 || norm === '') { closeDropdown(); return; }
        abortController = new AbortController();

        try {
            setLoading(true);
            results.innerHTML = '';
            resetActive();

            const shortcuts = findSearchShortcuts(rawValue) || [];
            shortcuts.forEach(s => results.appendChild(buildShortcutSearchResult(s)));

            const res = await fetch(`${basePath}manga/ajax/recherche/${encodeURIComponent(norm)}?t=${Date.now()}`, {
                signal: abortController.signal,
                headers: { 'X-Requested-With':'XMLHttpRequest' }
            });

            if (!res.ok) throw new Error('Erreur recherche live');
            const data = await res.json();
            const items = data.data?.results || [];

            if (!items.length && !shortcuts.length) { emptyState(); return; }
            items.forEach(i => results.appendChild(buildMangaSearchResult(i, rawValue)));

            openDropdown();
            activeIndex = 0;
            updateActive();

            // Ajout : survol souris met à jour activeIndex
            getItems().forEach((item, idx) => {
                item.addEventListener('mouseenter', () => {
                    activeIndex = idx;
                    updateActive();
                });
            });

        } catch (e) {
            if (e.name !== 'AbortError') { emptyState('Erreur de chargement'); console.error(e); }
        } finally { setLoading(false); }
    };

    form.addEventListener('submit', e => {
        e.preventDefault();
        let v = input.value.trim();
        if (v === '') return;
        v = normalizeSearchQuery(v);
        if (v === '') return;
        closeDropdown();
        window.location.href = `${basePath}manga/recherche/${encodeURIComponent(v)}`;
    });

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        resetActive();
        debounceTimer = setTimeout(() => fetchSearchResults(input.value.trim()), 250);
    });

    input.addEventListener('keydown', e => {
        const items = getItems();
        if (e.key === 'Escape') { e.preventDefault(); closeDropdown(); return; }
        if (!dropdown.classList.contains('has-results') || !items.length) return;

        if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = activeIndex < items.length-1 ? activeIndex+1 : 0; updateActive(); return; }
        if (e.key === 'ArrowUp') { e.preventDefault(); activeIndex = activeIndex>0 ? activeIndex-1 : items.length-1; updateActive(); return; }
        if (e.key === 'Enter') { 
            const item = items[activeIndex]; 
            if (!item) return; 
            e.preventDefault(); 
            window.location.href=item.href; 
        }
    });

    document.addEventListener('click', e => {
        const clickedInsideForm = e.target.closest('.js-header-search');
        const clickedInsideDropdown = e.target.closest('.js-header-search-dropdown');
        if (!clickedInsideForm && !clickedInsideDropdown) closeDropdown();
    });
}

// Auto-init
document.addEventListener('DOMContentLoaded', () => initSearchManga());