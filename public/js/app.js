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
// Navigation
// ==================================================

import {
    initAjaxNavigation,
} from './navigation/ajax-navigation.js';

import {
    initPrefetchNavigation,
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
// Helpers
// ==================================================

function safeInit(
    callback,
    label,
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

function isAppInitialized()
{
    return (
        document.body.dataset[
            APP_READY_ATTRIBUTE
        ]
        === 'true'
    );
}

function setAppInitialized()
{
    document.body.dataset[
        APP_READY_ATTRIBUTE
    ] = 'true';
}

// ==================================================
// App
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

    // ==============================================
    // Core
    // ==============================================

    safeInit(
        initPageTransitions,
        'PageTransitions',
    );

    // ==============================================
    // Navigation
    // ==============================================

    safeInit(
        initAjaxNavigation,
        'AjaxNavigation',
    );

    safeInit(
        initPrefetchNavigation,
        'PrefetchNavigation',
    );

    safeInit(
        initGlobalBackNavigation,
        'GlobalBackNavigation',
    );

    // ==============================================
    // Manga Pages
    // ==============================================

    safeInit(
        initAjouterPage,
        'AjouterPage',
    );

    safeInit(
        initModifierPage,
        'ModifierPage',
    );

    // ==============================================
    // Manga Actions
    // ==============================================

    safeInit(
        initUpdateNote,
        'UpdateNote',
    );

    safeInit(
        initDeleteManga,
        'DeleteManga',
    );

    safeInit(
        initUpdateReadStatus,
        'UpdateReadStatus',
    );

    safeInit(
        initSearchManga,
        'SearchManga',
    );

    // ==============================================
    // Manga Keyboard
    // ==============================================

    safeInit(
        initSeriesKeyboardNavigation,
        'SeriesKeyboardNavigation',
    );

    // ==============================================
    // Chinois
    // ==============================================

    safeInit(
        initToggleGrammaireMaitrise,
        'ToggleGrammaireMaitrise',
    );

    // ==============================================
    // Toast
    // ==============================================

    safeInit(
        initFlashToast,
        'FlashToast',
    );

    debug(
        'APP',
        '✅ Ready',
    );
}

// ==================================================
// Boot
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