// =========================================
// ROUTER HOOKS
// =========================================

import {
    debugError,
} from '../core/debug.js';

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
    for (
        const callback
        of beforeRouteChangeCallbacks
    )
    {
        try {

            await callback(
                context,
            );

        } catch (error) {

            debugError(
                'ROUTER-HOOK',
                error,
            );
        }
    }
}

// =========================================
// TRIGGER AFTER
// =========================================

export async function triggerRouteChange(
    context,
)
{
    for (
        const callback
        of routeChangeCallbacks
    )
    {
        try {

            await callback(
                context,
            );

        } catch (error) {

            debugError(
                'ROUTER-HOOK',
                error,
            );
        }
    }
}