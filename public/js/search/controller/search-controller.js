import { navigateTo } from '../../router/navigation/navigation.js';
let searchVersion = 0;
let lastQuery = null;
// =================================================
// CONTRÔLEUR DE RECHERCHE
// =================================================

import { $, $$ } from '../../core/dom.js';

import { fetchSearchResults } from '../api/search-api.js';

import { findSearchShortcuts } from '../shortcuts/search-shortcuts.js';

import { normalizeSearchQuery } from '../utils/search-utils.js';

import { openSearchDropdown, closeSearchDropdown, clearSearchResults } from '../ui/search-dropdown.js';

let rendererPromise;
function loadRenderer()
{
    return rendererPromise ??= import('../renderers/results-renderer.js').catch(error =>
    {
        rendererPromise = undefined;
        throw error;
    });
}

import { updateActiveResult } from './search-keyboard.js';

// =================================================
// CONFIGURATION
// =================================================

const SEARCH_DELAY = 200;

// =================================================
// ÉTAT
// =================================================

let debounceTimer = null;

let abortController = null;

let activeIndex = -1;

// =================================================
// INITIALISATION
// =================================================

export function initSearchController()
{
    const search = $('.js-header-search');

    const searchInput = $('#header-search-input');

    const searchResults = $('#header-search-results');

    const searchDropdown = $('.js-header-search-dropdown');

    if (!search || !searchInput || !searchResults || !searchDropdown)
    {
        return;
    }

    if (search.dataset.initialized === 'true')
    {
        return;
    }

    search.dataset.initialized = 'true';
    document.addEventListener('search:invalidate', () => resetSearch(searchInput, searchResults, searchDropdown));
    searchResults.addEventListener('mouseover', event =>
    {
        const item = event.target.closest('.search-result-item');
        if (!item || !searchResults.contains(item) || item.contains(event.relatedTarget)) return;
        activeIndex = Number(item.dataset.index);
        updateActiveResult(searchResults, activeIndex);
    });
    searchResults.addEventListener('click', event =>
    {
        const item = event.target.closest('.search-result-item');
        if (!item || !searchResults.contains(item) || event.button !== 0
            || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        resetSearch(searchInput, searchResults, searchDropdown);
        void navigateTo(item.href);
    });

    searchInput.addEventListener(
        'input',
        () =>
        {
            searchVersion++;
            abortController?.abort();
            lastQuery = null;
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(
                    () =>
                    {
                        void handleSearch(search, searchInput, searchResults, searchDropdown);
                    },
                    SEARCH_DELAY
                );
        }
    );

    search.addEventListener(
        'submit',
        (event) =>
        {
            event.preventDefault();

            clearTimeout(debounceTimer);

            void handleSearch(search, searchInput, searchResults, searchDropdown);
        }
    );

    searchInput.addEventListener(
        'keydown',
        (event) =>
        {
            handleKeyboardNavigation(event, searchInput, searchResults, searchDropdown);
        }
    );

    document.addEventListener(
        'click',
        (event) =>
        {
            if (!event.target.closest( '.js-header-search' ))
            {
                resetSearch(searchInput, searchResults, searchDropdown);
            }
        }
    );
}

// =================================================
// TRAITEMENT RECHERCHE
// =================================================

async function handleSearch(search, searchInput, searchResults, searchDropdown)
{
    const rawValue = searchInput.value;

    const query = normalizeSearchQuery(rawValue);

    if (query === '')
    {
        resetSearch(searchInput, searchResults, searchDropdown);

        return;
    }

    if (query === lastQuery) return;
    abortController?.abort();
    lastQuery = query;
    const version = ++searchVersion;
    abortController = new AbortController();

    activeIndex = -1;

    try
    {

        const basePath = search.dataset.basePath
            ?? '/';

        const [data, {renderResults}] = await Promise.all([
            fetchSearchResults(`${basePath}recherche?q=${encodeURIComponent(query)}`, abortController.signal),
            loadRenderer()
        ]);
        const {mangas = [], artbooks = [], chinois = [], figurines = [], nendoroids = [], peluches = []} = data;
        const shortcuts = findSearchShortcuts(query);
        if (version !== searchVersion || searchInput.value !== rawValue) return;

        renderResults({
            mangas,
            artbooks,
            chinois,
            figurines,
            nendoroids,
            peluches,
            shortcuts,
            rawValue,
            basePath,
            searchInput,
            searchResults,
            searchDropdown,
            setupResultItem,
            openDropdown,
            closeDropdown
        });

    } catch (error)
    {
        if (version === searchVersion) lastQuery = null;

        if (error?.name === 'AbortError')
        {
            return;
        }
    }
}

// =================================================
// PRÉPARATION ÉLÉMENT
// =================================================

function setupResultItem(item, index, searchInput, searchResults, searchDropdown)
{
    item.dataset.index = index;
}

// =================================================
// NAVIGATION AU CLAVIER
// =================================================

function handleKeyboardNavigation(event, searchInput, searchResults, searchDropdown)
{
    const resultItems = $$('.search-result-item', searchResults);

    if (event.key === 'ArrowDown')
    {

        if (!resultItems.length)
        {
            return;
        }

        event.preventDefault();

        activeIndex++;

        if (activeIndex >= resultItems.length)
        {
            activeIndex = 0;
        }

        updateActiveResult(searchResults, activeIndex);

        return;
    }

    if (event.key === 'ArrowUp')
    {

        if (!resultItems.length)
        {
            return;
        }

        event.preventDefault();

        activeIndex--;

        if (activeIndex < 0)
        {
            activeIndex = resultItems.length - 1;
        }

        updateActiveResult(searchResults, activeIndex);

        return;
    }

    if (event.key === 'Enter')
    {

        const activeItem = resultItems[activeIndex];

        if (activeItem)
        {

            event.preventDefault();

            resetSearch(searchInput, searchResults, searchDropdown);

            void navigateTo(activeItem.href);
        }
    }

    if (event.key === 'Escape')
    {
        resetSearch(searchInput, searchResults, searchDropdown);
    }
}

// =================================================
// MENU DÉROULANT
// =================================================

function openDropdown(searchDropdown)
{
    searchDropdown.classList.remove('is-loading');

    openSearchDropdown(searchDropdown);
}

function closeDropdown(searchDropdown)
{
    closeSearchDropdown(searchDropdown);

    searchDropdown.classList.remove('is-loading');
}

// =================================================
// RÉINITIALISATION RECHERCHE
// =================================================

function resetSearch(searchInput, searchResults, searchDropdown)
{
    clearTimeout(debounceTimer);
    searchVersion++;
    lastQuery = null;
    abortController?.abort();

    activeIndex = -1;

    searchInput.value = '';

    clearSearchResults(searchResults);

    closeDropdown(searchDropdown);
}
