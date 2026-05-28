// ==================================================
// APP INIT
// ==================================================

import {
    debug,
    debugError,
} from '../core/debug/debug.js';

import {
    showToast,
} from '../core/toast.js';

import {
    handleError,
} from '../core/errors/error-handler.js';

import {
    onRouteChange,
} from '../router/router-hooks.js';

import {
    GLOBAL_INITIALIZERS,
} from '../initializers/global-initializers.js';

import {
    ROUTE_INITIALIZERS,
} from '../routes/route-initializers.js';

import {
    initAppDebug,
} from './app-debug.js';

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

        handleError(
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
// ROUTE INITIALIZERS
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
// INIT
// ==================================================

export function initApp()
{
    debug(
        'APP',
        '🚀 Boot',
    );

    initAppDebug();

    runGlobalInitializers();

    runRouteInitializers();

    onRouteChange(
        () =>
        {
            runRouteInitializers();
        },
    );

    safeInit(
        'FlashToast',
        initFlashToast,
    );

    debug(
        'APP',
        '✅ Ready',
    );
}