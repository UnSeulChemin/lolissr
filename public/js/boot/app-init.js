// ==================================================
// APP INIT
// ==================================================

import {
    debug,
    debugError,
} from '../core/debug/debug.js';

import {
    end,
    start,
} from '../core/debug/profiler.js';

import {
    FrontendError,
} from '../core/errors/FrontendError.js';

import {
    handleError,
} from '../core/errors/error-handler.js';

import {
    showToast,
} from '../core/toast.js';

import {
    GLOBAL_INITIALIZERS,
} from '../initializers/global-initializers.js';

import {
    ROUTE_INITIALIZERS,
} from '../routes/route-initializers.js';

import {
    onRouteChange,
} from '../router/router-hooks.js';

import {
    initAppDebug,
} from './app-debug.js';

// ==================================================
// SAFE INIT
// ==================================================

async function safeInit(
    label,
    callback,
)
{
    start(label);

    try
    {
        await callback();

        debug(
            'INIT',
            `✅ ${label}`,
        );
    }
    catch (error)
    {
        debugError(
            'INIT',
            error,
        );

        handleError(
            error instanceof Error
                ? error
                : new FrontendError(
                    `Erreur pendant "${label}"`,
                    {
                        cause: error,
                    },
                ),
        );
    }
    finally
    {
        end(label);
    }
}

// ==================================================
// FLASH TOAST
// ==================================================

function initFlashToast()
{
    const flashToast = window.flashToast;

    if (! flashToast?.message)
    {
        return;
    }

    showToast(
        flashToast.message,
        flashToast.type ?? 'success',
    );
}

// ==================================================
// GLOBAL INITIALIZERS
// ==================================================

async function runGlobalInitializers()
{
    for (const [label, init] of GLOBAL_INITIALIZERS)
    {
        await safeInit(
            label,
            init,
        );
    }
}

// ==================================================
// ROUTE INITIALIZERS
// ==================================================

async function runRouteInitializers()
{
    const path = location.pathname;

    for (const { match, initializers } of ROUTE_INITIALIZERS)
    {
        if (! match.test(path))
        {
            continue;
        }

        for (const [label, init] of initializers)
        {
            await safeInit(
                label,
                init,
            );
        }
    }
}

// ==================================================
// INIT
// ==================================================

export async function initApp()
{
    debug(
        'APP',
        '🚀 Boot',
    );

    initAppDebug();

    await runGlobalInitializers();
    await runRouteInitializers();

    onRouteChange(
        runRouteInitializers,
    );

    await safeInit(
        'FlashToast',
        initFlashToast,
    );

    debug(
        'APP',
        '✅ Ready',
    );
}