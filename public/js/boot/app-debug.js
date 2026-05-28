// =========================================
// APP DEBUG
// =========================================

import {
    showToast,
} from '../core/toast.js';

// =========================================
// HELPERS
// =========================================

function reload()
{
    window.setTimeout(
        () =>
        {
            location.reload();
        },
        300,
    );
}

// =========================================
// INIT
// =========================================

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

            reload();
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

            reload();
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