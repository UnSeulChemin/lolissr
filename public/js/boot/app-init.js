import { runInitializers } from '../router/initializers/run-initializers.js';
// =================================================
// INITIALISATION DE L’APPLICATION
// =================================================

import { debug, debugError } from '../core/debug/debug.js';

import { FrontendError } from '../core/errors/FrontendError.js';

import { handleError } from '../core/errors/error-handler.js';

import { end, start } from '../core/debug/profiler.js';

import { initFlashToast } from '../core/toast.js';

import { appPath } from '../core/url.js';

import { GLOBAL_INITIALIZERS } from './global-initializers.js';

import { ROUTE_INITIALIZERS } from '../router/initializers/route-initializers.js';

import { onRouteChange } from '../router/router-hooks.js';

import { initAppDebug } from './app-debug.js';

// =================================================
// SÉCURISÉE INITIALISATION
// =================================================

async function safeInit(label, callback)
{
    start(label);

    try
    {
        await callback();

        debug('INIT', `✅ ${label}`);
    }
    catch (error)
    {
        debugError('INIT', error);

        handleError(
            error instanceof Error
                ? error
                : new FrontendError(
                    `Erreur pendant "${label}"`,
                    {
                        cause: error
                    }
                )
        );
    }
    finally
    {
        end(label);
    }
}

// =================================================
// NOTIFICATION DE SESSION
// =================================================

// =================================================
// GLOBAL INITIALISATIONS
// =================================================

async function runGlobalInitializers()
{
    for (const [label, init] of GLOBAL_INITIALIZERS)
    {
        await safeInit(label, init);
    }
}

// =================================================
// ROUTE INITIALISATIONS
// =================================================

let routeGeneration = 0;

async function runRouteInitializers()
{
    const generation = ++routeGeneration;
    const path = appPath();
    const initializers = ROUTE_INITIALIZERS
        .filter(({match}) => match.test(path))
        .flatMap(({initializers}) => initializers);
    await runInitializers(initializers, safeInit, () => generation === routeGeneration && path === appPath());
}
// =================================================
// INITIALISATION
// =================================================

export async function initApp()
{
    debug('APP', '🚀 Boot');

    initAppDebug();

    // S’abonner avant la navigation du routeur, y compris pendant le démarrage asynchrone.
    const initialGeneration = routeGeneration;
    onRouteChange(runRouteInitializers);

    await runGlobalInitializers();
    // Une navigation pendant l’initialisation globale initialise déjà sa route.
    if (routeGeneration === initialGeneration) await runRouteInitializers();

    await safeInit('FlashToast', initFlashToast);

    debug('APP', '✅ Ready');
}
