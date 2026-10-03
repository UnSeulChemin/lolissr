// =================================================
// ROUTEUR POINTS D’EXTENSION
// =================================================

import { debugError } from '../core/debug/debug.js';

// =================================================
// ÉTAT
// =================================================

const routeChangeCallbacks = new Set();

// =================================================
// ENREGISTREMENT
// =================================================

function registerCallback(callbacks, callback)
{
    callbacks.add(callback);

    return () =>
    {
        callbacks.delete(callback);
    };
}

export function onRouteChange(callback)
{
    return registerCallback(routeChangeCallbacks, callback);
}

// =================================================
// EXÉCUTION
// =================================================

async function runCallbacks(callbacks, context)
{
    const tasks = [];

    for (const callback of callbacks)
    {
        tasks.push(
            Promise.resolve()
                .then(() => callback(context))
                .catch(
                    error =>
                    {
                        debugError('ROUTER-HOOK', error);
                    }
                )
        );
    }

    await Promise.all(tasks);
}

// =================================================
// DÉCLENCHEURS
// =================================================

export async function triggerRouteChange(context)
{
    await runCallbacks(routeChangeCallbacks, context);
}