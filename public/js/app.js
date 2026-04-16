import { initPaginationAjax } from './features/pagination.js';
import { initAjaxNotes } from './features/notes.js';
import { initAutoSlug } from './features/slug.js';
import { showToast } from './core/toast.js';
import { initCardPrefetch } from './features/prefetch.js';
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
});