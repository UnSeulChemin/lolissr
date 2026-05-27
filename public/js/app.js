// ==================================================
// APP
// ==================================================

import {
    debug,
    debugError,
} from './core/debug.js';

import {
    showToast,
} from './core/toast.js';

// ==================================================
// ROUTER
// ==================================================

import {
    initRouter,
} from './router/router.js';

import {
    initPrefetch,
} from './router/prefetch.js';

import {
    onRouteChange,
} from './router/router-hooks.js';

// ==================================================
// NAVIGATION
// ==================================================

import {
    initGlobalBackNavigation,
} from './history/back-navigation.js';

// ==================================================
// MANGA PAGES
// ==================================================

import {
    initAjouterPage,
} from './manga/pages/ajouter.js';

import {
    initModifierPage,
} from './manga/pages/modifier.js';

// ==================================================
// MANGA ACTIONS
// ==================================================

import {
    initUpdateNote,
} from './manga/actions/update-note.js';

import {
    initDeleteManga,
} from './manga/actions/delete-manga.js';

import {
    initUpdateReadStatus,
} from './manga/actions/update-read-status.js';

// ==================================================
// MANGA SEARCH
// ==================================================

import {
    initSearchController,
} from './manga/search/controller/search-controller.js';

// ==================================================
// MANGA KEYBOARD
// ==================================================

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
        !flashToast?.message
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

    [
        'Router',
        initRouter,
    ],

    [
        'Prefetch',
        initPrefetch,
    ],

    [
        'GlobalBackNavigation',
        initGlobalBackNavigation,
    ],
];

// ==================================================
// ROUTE INITIALIZERS
// ==================================================

const ROUTE_INITIALIZERS = [

    // ==============================================
    // AJOUTER
    // ==============================================

    {
        match:
            /^\/lolissr\/manga\/ajouter\/?$/,

        initializers:
        [
            [
                'AjouterPage',
                initAjouterPage,
            ],
        ],
    },

    // ==============================================
    // MODIFIER
    // ==============================================

    {
        match:
            /^\/lolissr\/manga\/series\/modifier\/.+/,

        initializers:
        [
            [
                'ModifierPage',
                initModifierPage,
            ],
        ],
    },

    // ==============================================
    // GLOBAL ROUTES
    // ==============================================

    {
        match:
            /^\/lolissr\/?/,

        initializers:
        [
            [
                'UpdateNote',
                initUpdateNote,
            ],

            [
                'DeleteManga',
                initDeleteManga,
            ],

            [
                'UpdateReadStatus',
                initUpdateReadStatus,
            ],

            [
                'SearchController',
                initSearchController,
            ],

            [
                'SeriesKeyboardNavigation',
                initSeriesKeyboardNavigation,
            ],

            [
                'ToggleGrammaireMaitrise',
                initToggleGrammaireMaitrise,
            ],
        ],
    },
];

// ==================================================
// RUN GLOBAL INITIALIZERS
// ==================================================

function runGlobalInitializers()
{
    for (
        const [
            label,
            init,
        ]
        of GLOBAL_INITIALIZERS
    )
    {
        safeInit(
            label,
            init,
        );
    }
}

// ==================================================
// RUN ROUTE INITIALIZERS
// ==================================================

function runRouteInitializers()
{
    const path =
        location.pathname;

    for (
        const route
        of ROUTE_INITIALIZERS
    )
    {
        if (
            !route.match.test(
                path,
            )
        ) {
            continue;
        }

        for (
            const [
                label,
                init,
            ]
            of route.initializers
        )
        {
            safeInit(
                label,
                init,
            );
        }
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

    // ==============================================
    // GLOBAL
    // ==============================================

    runGlobalInitializers();

    // ==============================================
    // CURRENT ROUTE
    // ==============================================

    runRouteInitializers();

    // ==============================================
    // ROUTER HOOK
    // ==============================================

    onRouteChange(
        () =>
        {
            runRouteInitializers();
        },
    );

    // ==============================================
    // FLASH TOAST
    // ==============================================

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
    document.readyState ===
    'loading'
) {

    document.addEventListener(
        'DOMContentLoaded',
        initApp,
        {
            once:
                true,
        },
    );

} else {

    initApp();
}