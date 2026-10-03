// =================================================
// NETTOYAGE DU ROUTEUR
// =================================================

import { debugError } from '../core/debug/debug.js';

// =================================================
// ÉTAT
// =================================================

const cleanupCallbacks = new Set();

// =================================================
// ENREGISTREMENT
// =================================================

export function registerCleanup(callback)
{
    if (typeof callback !== 'function')
    {
        return () =>
        {};
    }

    cleanupCallbacks.add(callback);

    return () =>
    {
        cleanupCallbacks.delete(callback);
    };
}

// =================================================
// EXÉCUTION
// =================================================

export function runCleanup()
{
    for (const callback of cleanupCallbacks)
    {
        try
        {

            callback();

        } catch (error)
        {

            debugError('ROUTER-CLEANUP', error);
        }
    }

    cleanupCallbacks.clear();
}