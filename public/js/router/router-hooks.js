// =========================================
// ROUTER HOOKS
// =========================================

import {
    debugError,
} from '../core/debug/debug.js';

// =========================================
// STATE
// =========================================

const beforeRouteChangeCallbacks = new Set();
const routeChangeCallbacks = new Set();

// =========================================
// REGISTRATION
// =========================================

function registerCallback(
    callbacks,
    callback,
)
{
    callbacks.add(callback);

    return () =>
    {
        callbacks.delete(callback);
    };
}

export function onBeforeRouteChange(
    callback,
)
{
    return registerCallback(
        beforeRouteChangeCallbacks,
        callback,
    );
}

export function onRouteChange(
    callback,
)
{
    return registerCallback(
        routeChangeCallbacks,
        callback,
    );
}

// =========================================
// EXECUTION
// =========================================

async function runCallbacks(
    callbacks,
    context,
)
{
    const tasks = [];

    for (const callback of callbacks)
    {
        tasks.push(
            Promise.resolve()
                .then(
                    () =>
                        callback(context),
                )
                .catch(
                    error =>
                    {
                        debugError(
                            'ROUTER-HOOK',
                            error,
                        );
                    },
                ),
        );
    }

    await Promise.all(tasks);
}

// =========================================
// TRIGGERS
// =========================================

export async function triggerBeforeRouteChange(
    context,
)
{
    await runCallbacks(
        beforeRouteChangeCallbacks,
        context,
    );
}

export async function triggerRouteChange(
    context,
)
{
    await runCallbacks(
        routeChangeCallbacks,
        context,
    );
}