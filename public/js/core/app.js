import { showToast } from './toast.js';

import { initLoadSeriesPage } from '../manga/actions/load-series-page.js';
import { initUpdateNote } from '../manga/actions/update-note.js';
import { initDeleteManga } from '../manga/actions/delete-manga.js';
import { initUpdateReadStatus } from '../manga/actions/update-read-status.js';
import { initSearchManga } from '../manga/actions/search-manga.js';

import { initPrefetchSeries } from '../manga/navigation/prefetch-series.js';
import { initPrefetchLinks } from '../manga/navigation/prefetch-links.js';
import { initSeriesKeyboardNavigation } from '../manga/navigation/series-keyboard-navigation.js';
import { initBackNavigation } from '../manga/navigation/back-navigation.js';

import { initAjouterPage } from '../manga/pages/ajouter.js';
import { initModifierPage } from '../manga/pages/modifier.js';

import { initToggleGrammaireMaitrise } from '../chinois/actions/toggle-grammar-mastery.js';

/*
|--------------------------------------------------------------------------
| Toast flash injecté côté PHP
|--------------------------------------------------------------------------
*/

function initFlashToast()
{
    if (!window.flashToast?.message)
    {
        return;
    }

    showToast(
        window.flashToast.message,
        window.flashToast.type || 'success'
    );
}

/*
|--------------------------------------------------------------------------
| Initialisation app
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    () =>
    {
        /*
        |------------------------------------------------------------------
        | Pages
        |------------------------------------------------------------------
        */

        initAjouterPage();
        initModifierPage();

        /*
        |------------------------------------------------------------------
        | AJAX
        |------------------------------------------------------------------
        */

        initLoadSeriesPage();
        initUpdateNote();
        initDeleteManga();
        initUpdateReadStatus();

        /*
        |------------------------------------------------------------------
        | Chinois AJAX
        |------------------------------------------------------------------
        */

        initToggleGrammaireMaitrise();

        /*
        |------------------------------------------------------------------
        | Navigation
        |------------------------------------------------------------------
        */

        initSeriesKeyboardNavigation();
        initBackNavigation();

        /*
        |------------------------------------------------------------------
        | Prefetch
        |------------------------------------------------------------------
        */

        initPrefetchSeries();
        initPrefetchLinks();

        /*
        |------------------------------------------------------------------
        | Recherche
        |------------------------------------------------------------------
        */

        initSearchManga();

        /*
        |------------------------------------------------------------------
        | Toast
        |------------------------------------------------------------------
        */

        initFlashToast();
    }
);