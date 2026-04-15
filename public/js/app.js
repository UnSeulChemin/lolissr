import { initPaginationAjax } from './pagination.js';
import { initAjaxNotes } from './notes.js';
import { initAutoSlug } from './slug.js';
import { showToast } from './toast.js';
import { initCardPrefetch } from './prefetch.js';
import { initLiveSearch } from './search.js';

document.addEventListener('DOMContentLoaded', () =>
{
    initPaginationAjax();
    initAjaxNotes();
    initAutoSlug();
    initCardPrefetch();
    initLiveSearch();

    // Rend showToast accessible globalement
    window.showToast = showToast;
});