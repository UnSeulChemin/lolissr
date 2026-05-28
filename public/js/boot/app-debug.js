// ==================================================
// APP DEBUG
// ==================================================

import {
    showToast,
} from '../core/toast.js';

// ==================================================
// INIT
// ==================================================

export function initAppDebug()
{
    if (
        !window.location.hostname.includes(
            'localhost',
        )
    ) {

        return;
    }

    window.enableDebug =
        () =>
        {
            localStorage.setItem(
                'lolissr_debug',
                '1',
            );

            showToast(
                'Debug activé',
                'success',
            );

            window.setTimeout(
                () =>
                {
                    location.reload();
                },
                300,
            );
        };

    window.disableDebug =
        () =>
        {
            localStorage.removeItem(
                'lolissr_debug',
            );

            showToast(
                'Debug désactivé',
                'success',
            );

            window.setTimeout(
                () =>
                {
                    location.reload();
                },
                300,
            );
        };

    window.__TEST_ERROR__ =
        () =>
        {
            throw new Error(
                'Test error',
            );
        };

    window.__TEST_PROMISE_ERROR__ =
        () =>
        {
            Promise.reject(
                new Error(
                    'Promise test error',
                ),
            );
        };
}