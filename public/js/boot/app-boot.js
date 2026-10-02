// =================================================
// DÉMARRAGE DE L’APPLICATION
// =================================================

import {
    debugError,
} from '../core/debug/debug.js';

import {
    handleError,
} from '../core/errors/error-handler.js';

import {
    initApp,
} from './app-init.js';

// =================================================
// DÉMARRAGE
// =================================================

function startApp()
{
    void initApp()
        .catch(
            error =>
            {
                debugError(
                    'APP',
                    error,
                );

                handleError(
                    error,
                );
            },
        );
}

// =================================================
// DÉMARRAGE
// =================================================

export function bootApp()
{
    if (document.readyState === 'loading')
    {
        document.addEventListener(
            'DOMContentLoaded',
            startApp,
            {
                once: true,
            },
        );

        return;
    }

    startApp();
}