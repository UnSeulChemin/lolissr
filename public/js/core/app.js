// ==================================================
// App Initialization
// ==================================================

import {
    showToast,
} from './toast.js';

/*
|------------------------------------------------------------------
| Pages
|------------------------------------------------------------------
*/

import {
    initAjouterPage,
} from '../manga/pages/ajouter.js';

import {
    initModifierPage,
} from '../manga/pages/modifier.js';

/*
|------------------------------------------------------------------
| Manga Actions
|------------------------------------------------------------------
*/

import {
    initLoadSeriesPage,
} from '../manga/actions/load-series-page.js';

import {
    initUpdateNote,
} from '../manga/actions/update-note.js';

import {
    initDeleteManga,
} from '../manga/actions/delete-manga.js';

import {
    initUpdateReadStatus,
} from '../manga/actions/update-read-status.js';

import {
    initSearchManga,
} from '../manga/actions/search-manga.js';

/*
|------------------------------------------------------------------
| Navigation
|------------------------------------------------------------------
*/

import {
    initPrefetchSeries,
} from '../manga/navigation/prefetch-series.js';

import {
    initSeriesKeyboardNavigation,
} from '../manga/navigation/series-keyboard-navigation.js';

import {
    initGlobalBackNavigation,
} from '../manga/navigation/global-back-navigation.js';

/*
|------------------------------------------------------------------
| Chinois
|------------------------------------------------------------------
*/

import {
    initToggleGrammaireMaitrise,
} from '../chinois/actions/toggle-grammar-mastery.js';

/*
|------------------------------------------------------------------
| Flash Toast
|------------------------------------------------------------------
*/

function initFlashToast()
{
    if (
        !window.flashToast?.message
    ) {
        return;
    }

    showToast(
        window.flashToast.message,
        window.flashToast.type
        || 'success',
    );
}

/*
|------------------------------------------------------------------
| Safe Init
|------------------------------------------------------------------
*/

function safeInit(
    callback,
    label,
)
{
    try {

        callback();

        console.log(
            `✅ ${label}`,
        );

    } catch (error) {

        console.error(
            `❌ ${label}`,
            error,
        );
    }
}

/*
|------------------------------------------------------------------
| App Init
|------------------------------------------------------------------
*/

function initApp()
{
    /*
    |--------------------------------------------------------------
    | Prevent double init
    |--------------------------------------------------------------
    */

    if (
        document.body.dataset
            .appInitialized
        === 'true'
    ) {
        return;
    }

    document.body.dataset
        .appInitialized =
            'true';

    console.log(
        '🚀 APP INIT',
    );

    /*
    |--------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------
    */

    safeInit(
        initAjouterPage,
        'initAjouterPage',
    );

    safeInit(
        initModifierPage,
        'initModifierPage',
    );

    /*
    |--------------------------------------------------------------
    | Manga Actions
    |--------------------------------------------------------------
    */

    safeInit(
        initLoadSeriesPage,
        'initLoadSeriesPage',
    );

    safeInit(
        initUpdateNote,
        'initUpdateNote',
    );

    safeInit(
        initDeleteManga,
        'initDeleteManga',
    );

    safeInit(
        initUpdateReadStatus,
        'initUpdateReadStatus',
    );

    safeInit(
        initSearchManga,
        'initSearchManga',
    );

    /*
    |--------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------
    */

    safeInit(
        initPrefetchSeries,
        'initPrefetchSeries',
    );

    safeInit(
        initSeriesKeyboardNavigation,
        'initSeriesKeyboardNavigation',
    );

    safeInit(
        initGlobalBackNavigation,
        'initGlobalBackNavigation',
    );

    /*
    |--------------------------------------------------------------
    | Chinois
    |--------------------------------------------------------------
    */

    safeInit(
        initToggleGrammaireMaitrise,
        'initToggleGrammaireMaitrise',
    );

    /*
    |--------------------------------------------------------------
    | Toast
    |--------------------------------------------------------------
    */

    safeInit(
        initFlashToast,
        'initFlashToast',
    );

    console.log(
        '✅ APP READY',
    );
}

/*
|------------------------------------------------------------------
| DOM Ready
|------------------------------------------------------------------
*/

if (
    document.readyState
    === 'loading'
) {

    document.addEventListener(
        'DOMContentLoaded',
        initApp,
    );

} else {

    initApp();
}