import { initPaginationAjax } from './pagination.js';
import { initAjaxNotes } from './notes.js';
import { initAutoSlug } from './slug.js';

document.addEventListener('DOMContentLoaded', () =>
{
    initPaginationAjax();
    initAjaxNotes();
    initAutoSlug();
});