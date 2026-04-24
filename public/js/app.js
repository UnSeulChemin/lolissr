import { initCollectionPaginationAjax } from './features/ajax-pagination.js';
import { initMangaAjaxNotes } from './features/ajax-notes.js';
import { initMangaAjaxDelete } from './features/ajax-delete.js';
import { initAutoSlug } from './features/slug.js';
import { showToast } from './core/toast.js';
import { initCollectionCardPrefetch } from './features/prefetch-collection.js';
import { initLiveSearch } from './features/search.js';
import { initCollectionKeyboardNavigation } from './features/collection-keyboard-navigation.js';
import { initLinkPreloading } from './features/prefetch-links.js';
import { initAjouterPage } from './pages/ajouter.js';
import { initEditPage } from './pages/modifier.js';

/**
 * Affiche un toast flash injecté côté PHP.
 */
function initFlashToast()
{
    if (!window.flashToast || !window.flashToast.message)
    {
        return;
    }

    showToast(
        window.flashToast.message,
        window.flashToast.type || 'success'
    );
}

window.addEventListener('load', () =>
{
    const container = document.querySelector('.collection-ajax-container');

    if (!container)
    {
        return;
    }

    container.classList.remove('is-loading');
});

document.addEventListener('DOMContentLoaded', () =>
{
    /*
    |------------------------------------------------------------------
    | PAGES
    |------------------------------------------------------------------
    */

    initAutoSlug();
    initAjouterPage();
    initEditPage();

    /*
    |------------------------------------------------------------------
    | AJAX
    |------------------------------------------------------------------
    */

    initCollectionPaginationAjax();
    initMangaAjaxNotes();
    initMangaAjaxDelete();

    /*
    |------------------------------------------------------------------
    | NAVIGATION
    |------------------------------------------------------------------
    */

    initCollectionKeyboardNavigation();

    /*
    |------------------------------------------------------------------
    | PREFETCH
    |------------------------------------------------------------------
    */

    initCollectionCardPrefetch();
    initLinkPreloading();

    /*
    |------------------------------------------------------------------
    | SEARCH
    |------------------------------------------------------------------
    */

    initLiveSearch();

    /*
    |------------------------------------------------------------------
    | TOAST
    |------------------------------------------------------------------
    */

    initFlashToast();
});