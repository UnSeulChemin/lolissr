import { initPaginationAjax } from './pagination.js';
import { initAjaxNotes } from './notes.js';
import { initAutoSlug } from './slug.js';
import { showToast } from './toast.js';

document.addEventListener('DOMContentLoaded', () =>
{
    initPaginationAjax();
    initAjaxNotes();
    initAutoSlug();

    // Rend showToast accessible globalement
    window.showToast = showToast;
});