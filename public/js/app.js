// ==================================================
// CORE
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
// NAVIGATION
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
// MANGA
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
// CHINOIS
// ==================================================

import {
    initToggleGrammaireMaitrise,
} from './chinois/actions/toggle-grammar-mastery.js';

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
// GLOBAL INITIALIZERS
// ==================================================

const GLOBAL_INITIALIZERS = [

    ['PageTransitions', initPageTransitions],

    ['AjaxNavigation', initAjaxNavigation],

    ['Prefetch', initPrefetch],

    ['GlobalBackNavigation', initGlobalBackNavigation],
];

// ==================================================
// PAGE INITIALIZERS
// ==================================================

const PAGE_INITIALIZERS = [

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
// RUNNERS
// ==================================================

function runGlobalInitializers()
{
    for (
        const [
            label,
            init,
        ] of GLOBAL_INITIALIZERS
    ) {

        safeInit(
            label,
            init,
        );
    }
}

function runPageInitializers()
{
    for (
        const [
            label,
            init,
        ] of PAGE_INITIALIZERS
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
    debug(
        'APP',
        '🚀 Boot',
    );

    // =============================================
    // GLOBAL
    // =============================================

    runGlobalInitializers();

    // =============================================
    // PAGE
    // =============================================

    runPageInitializers();

    // =============================================
    // AJAX PAGE RELOAD
    // =============================================

    document.addEventListener(
        'ajax:page-loaded',
        () =>
        {
            runPageInitializers();
        },
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