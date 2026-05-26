// ==================================================
// Core
// ==================================================

import {
    debug,
    debugError,
} from './core/debug.js';

import {
    initPageTransitions,
} from './core/page-transition.js';

import {
    showToast,
} from './core/toast.js';

// ==================================================
// Navigation (SPA)
// ==================================================

import {
    initAjaxNavigation,
} from './navigation/ajax-navigation.js';

import {
    initPrefetch,
} from './navigation/prefetch.js';

import {
    initGlobalBackNavigation,
} from './navigation/global-back-navigation.js';

// ==================================================
// Manga
// ==================================================

import {
    initAjouterPage,
} from './manga/pages/ajouter.js';

import {
    initModifierPage,
} from './manga/pages/modifier.js';

import {
    initUpdateNote,
} from './manga/actions/update-note.js';

import {
    initDeleteManga,
} from './manga/actions/delete-manga.js';

import {
    initUpdateReadStatus,
} from './manga/actions/update-read-status.js';

import {
    initSearchManga,
} from './manga/actions/search-manga.js';

import {
    initSeriesKeyboardNavigation,
} from './manga/keyboard/series-keyboard-navigation.js';

// ==================================================
// Chinois
// ==================================================

import {
    initToggleGrammaireMaitrise,
} from './chinois/actions/toggle-grammar-mastery.js';

// ==================================================
// Config
// ==================================================

const APP_READY_ATTRIBUTE =
    'appInitialized';

// ==================================================
// SAFE INIT
// ==================================================

function safeInit(
    label,
    callback,
)
{
    try {

        callback();

        debug(
            'INIT',
            `✅ ${label}`,
        );

    } catch (error) {

        debugError(
            label,
            error,
        );
    }
}

// ==================================================
// FLASH TOAST
// ==================================================

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

// ==================================================
// APP STATE
// ==================================================

function isAppInitialized()
{
    return (
        document.body.dataset[
            APP_READY_ATTRIBUTE
        ] === 'true'
    );
}

function setAppInitialized()
{
    document.body.dataset[
        APP_READY_ATTRIBUTE
    ] = 'true';
}

// ==================================================
// INITIALIZERS
// ==================================================

const INITIALIZERS = [

    ['PageTransitions', initPageTransitions],

    ['AjaxNavigation', initAjaxNavigation],

    ['Prefetch', initPrefetch],

    ['GlobalBackNavigation', initGlobalBackNavigation],

    ['AjouterPage', initAjouterPage],

    ['ModifierPage', initModifierPage],

    ['UpdateNote', initUpdateNote],

    ['DeleteManga', initDeleteManga],

    ['UpdateReadStatus', initUpdateReadStatus],

    ['SearchManga', initSearchManga],

    ['SeriesKeyboardNavigation', initSeriesKeyboardNavigation],

    ['ToggleGrammaireMaitrise', initToggleGrammaireMaitrise],
];

// ==================================================
// RUN
// ==================================================

function runInitializers()
{
    for (
        const [
            label,
            init,
        ] of INITIALIZERS
    ) {

        safeInit(
            label,
            init,
        );
    }
}

// ==================================================
// APP INIT
// ==================================================

function initApp()
{
    if (
        isAppInitialized()
    ) {
        return;
    }

    setAppInitialized();

    debug(
        'APP',
        '🚀 Boot',
    );

    // =============================================
    // FIRST LOAD
    // =============================================

    runInitializers();

    // =============================================
    // SPA RELOAD
    // =============================================

    document.addEventListener(
        'ajax:page-loaded',
        runInitializers,
    );

    // =============================================
    // FLASH
    // =============================================

    safeInit(
        'FlashToast',
        initFlashToast,
    );

    debug(
        'APP',
        '✅ Ready',
    );
}

// ==================================================
// BOOT
// ==================================================

if (
    document.readyState
    === 'loading'
) {

    document.addEventListener(
        'DOMContentLoaded',
        initApp,
        {
            once: true,
        },
    );

} else {

    initApp();
}