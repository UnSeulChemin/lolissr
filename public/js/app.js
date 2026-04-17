import { initPaginationAjax } from './features/ajax-pagination.js';
import { initAjaxNotes } from './features/ajax-notes.js';
import { initAutoSlug } from './features/slug.js';
import { showToast } from './core/toast.js';
import { initCardPrefetch } from './features/prefetch-navigation.js';
import { initLiveSearch } from './features/search.js';
import { initCollectionKeyboardNavigation } from './features/collection_keyboard.js';
import { initLinkPreloading } from './features/preload-links.js';

document.addEventListener('DOMContentLoaded', () =>
{
    initPaginationAjax();
    initAjaxNotes();
    initAutoSlug();
    initCardPrefetch();
    initLiveSearch();
    initCollectionKeyboardNavigation();
    initLinkPreloading();

    window.showToast = showToast;

    if (window.flashToast)
    {
        showToast(
            window.flashToast.message,
            window.flashToast.type
        );
    }
});