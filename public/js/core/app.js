// ==================================================
// Main App Initialization
// ==================================================
import { showToast } from './toast.js';

// Pages
import { initAjouterPage } from '../manga/pages/ajouter.js';
import { initModifierPage } from '../manga/pages/modifier.js';

// Manga Actions AJAX
import { initLoadSeriesPage } from '../manga/actions/load-series-page.js';
import { initUpdateNote } from '../manga/actions/update-note.js';
import { initDeleteManga } from '../manga/actions/delete-manga.js';
import { initUpdateReadStatus } from '../manga/actions/update-read-status.js';
import { initSearchManga } from '../manga/actions/search-manga.js';

// Prefetch & Navigation
import { initPrefetchSeries } from '../manga/navigation/prefetch-series.js';
import { initPrefetchLinks } from '../manga/navigation/prefetch-links.js';
import { initSeriesKeyboardNavigation } from '../manga/navigation/series-keyboard-navigation.js';
import { initBackNavigation } from '../manga/navigation/back-navigation.js';

// Chinois Actions
import { initToggleGrammaireMaitrise } from '../chinois/actions/toggle-grammar-mastery.js';

// --------------------------------------------------
// Flash toast injecté depuis PHP
// --------------------------------------------------
function initFlashToast() {
    if (!window.flashToast?.message) return;

    showToast(
        window.flashToast.message,
        window.flashToast.type || 'success',
    );
}

// --------------------------------------------------
// Initialisation de l'app
// --------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // Pages
    initAjouterPage();
    initModifierPage();

    // Manga AJAX actions
    initLoadSeriesPage();
    initUpdateNote();
    initDeleteManga();
    initUpdateReadStatus();

    // Chinois AJAX
    initToggleGrammaireMaitrise();

    // Navigation globale
    initSeriesKeyboardNavigation();
    initBackNavigation();

    // Prefetch
    initPrefetchSeries();
    initPrefetchLinks();

    // Recherche
    initSearchManga();

    // Flash toast
    initFlashToast();
});