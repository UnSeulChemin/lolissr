// =========================================
// ROUTER HOOKS
// =========================================

import {
    debugError,
} from '../core/debug/debug.js';

// =========================================
// STATE
// =========================================

const beforeRouteChangeCallbacks =
    new Set();

const routeChangeCallbacks =
    new Set();

// =========================================
// REGISTER BEFORE
// =========================================

export function onBeforeRouteChange(
    callback,
)
{
    beforeRouteChangeCallbacks.add(
        callback,
    );

    return () =>
    {
        beforeRouteChangeCallbacks.delete(
            callback,
        );
    };
}

// =========================================
// REGISTER AFTER
// =========================================

export function onRouteChange(
    callback,
)
{
    routeChangeCallbacks.add(
        callback,
    );

    return () =>
    {
        routeChangeCallbacks.delete(
            callback,
        );
    };
}

// =========================================
// TRIGGER BEFORE
// =========================================

export async function triggerBeforeRouteChange(
    context,
)
{
    const tasks =
        [];

    for (
        const callback
        of beforeRouteChangeCallbacks
    )
    {
        tasks.push(
            Promise.resolve()
                .then(
                    () =>
                        callback(
                            context,
                        ),
                )
                .catch(
                    (
                        error,
                    ) =>
                    {
                        debugError(
                            'ROUTER-HOOK',
                            error,
                        );
                    },
                ),
        );
    }

    await Promise.all(
        tasks,
    );
}

// =========================================
// TRIGGER AFTER
// =========================================

export async function triggerRouteChange(
    context,
)
{
    const tasks =
        [];

    for (
        const callback
        of routeChangeCallbacks
    )
    {
        tasks.push(
            Promise.resolve()
                .then(
                    () =>
                        callback(
                            context,
                        ),
                )
                .catch(
                    (
                        error,
                    ) =>
                    {
                        debugError(
                            'ROUTER-HOOK',
                            error,
                        );
                    },
                ),
        );
    }

    await Promise.all(
        tasks,
    );
}