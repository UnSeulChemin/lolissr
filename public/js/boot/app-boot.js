// ==================================================
// APP BOOT
// ==================================================

import {
    initApp,
} from './app-init.js';

// ==================================================
// BOOT
// ==================================================

export function bootApp()
{
    if (
        document.readyState
        === 'loading'
    ) {

        document.addEventListener(
            'DOMContentLoaded',
            initApp,
            {
                once:
                    true,
            },
        );

        return;
    }

    initApp();
}