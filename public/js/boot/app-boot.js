// ==================================================
// APP BOOT
// ==================================================

import {
    debugError,
} from '../core/debug/debug.js';

import {
    handleError,
} from '../core/errors/error-handler.js';

import {
    initApp,
} from './app-init.js';

// ==================================================
// START
// ==================================================

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

// ==================================================
// BOOT
// ==================================================

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