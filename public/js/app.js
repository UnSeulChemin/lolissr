import { initPaginationAjax } from './features/ajax-pagination.js';
import { initAjaxNotes } from './features/ajax-notes.js';
import { initAutoSlug } from './features/slug.js';
import { showToast } from './core/toast.js';
import { initCardPrefetch } from './features/prefetch-collection.js';
import { initLiveSearch } from './features/search.js';
import { initCollectionKeyboardNavigation } from './features/collection_keyboard.js';
import { initLinkPreloading } from './features/prefetch-links.js';
import { initAjouterPage } from './pages/ajouter.js';
import { initEditPage } from './pages/edit.js';

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

document.addEventListener('DOMContentLoaded', () =>
{
    /* Core UI */
    initAutoSlug();
    initAjouterPage();
    initEditPage();

    /* AJAX */
    initPaginationAjax();
    initAjaxNotes();

    /* Navigation */
    initCollectionKeyboardNavigation();

    /* Prefetch */
    initCardPrefetch();
    initLinkPreloading();

    /* Search (dernier) */
    initLiveSearch();

    /* Toast flash */
    initFlashToast();
});