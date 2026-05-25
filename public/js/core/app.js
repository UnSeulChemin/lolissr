// ==================================================
// App Initialization
// ==================================================

import {
    showToast,
} from './toast.js';

/*
|------------------------------------------------------------------
| Page Transition
|------------------------------------------------------------------
*/

import {
    initPageTransitions,
} from './page-transition.js';

/*
|------------------------------------------------------------------
| Debug
|------------------------------------------------------------------
*/

const DEBUG =
    window.location.hostname
    === 'localhost';

function debugLog(
    ...args
)
{
    if (!DEBUG) {
        return;
    }

    console.log(
        ...args,
    );
}

function debugError(
    ...args
)
{
    if (!DEBUG) {
        return;
    }

    console.error(
        ...args,
    );
}

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
    initAjaxNavigation,
} from '../manga/navigation/ajax-navigation.js';

import {
    initPrefetchNavigation,
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
    const flashToast =
        window.flashToast;

    if (
        !flashToast
        || !flashToast.message
    ) {
        return;
    }

    showToast(
        flashToast.message,
        flashToast.type
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

        debugLog(
            `✅ ${label}`,
        );

    } catch (error) {

        debugError(
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

    debugLog(
        '🚀 APP INIT',
    );

    /*
    |--------------------------------------------------------------
    | Core
    |--------------------------------------------------------------
    */

    safeInit(
        initPageTransitions,
        'initPageTransitions',
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
        initAjaxNavigation,
        'initAjaxNavigation',
    );

    safeInit(
        initPrefetchNavigation,
        'initPrefetchNavigation',
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

    debugLog(
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