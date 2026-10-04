// =================================================
// DÉBOGAGE DE L’APPLICATION
// =================================================

import { showToast } from '../core/toast.js';
import { config } from '../core/config.js';
import { enableDebug, disableDebug } from '../core/debug/debug-storage.js';

// =================================================
// UTILITAIRES
// =================================================

function reload()
{
    window.setTimeout(
        () =>
        {
            location.reload();
        },
        300
    );
}

// =================================================
// INITIALISATION
// =================================================

export function initAppDebug()
{
    if (!config.isLocalhost)
    {

        return;
    }

    window.enableDebug = () =>
        {
            enableDebug();

            showToast('Debug activé', 'success');

            reload();
        };

    window.disableDebug = () =>
        {
            disableDebug();

            showToast('Debug désactivé', 'success');

            reload();
        };

    window.__TEST_ERROR__ = () =>
        {
            throw new Error('Test error');
        };

    window.__TEST_PROMISE_ERROR__ = () =>
        {
            Promise.reject(new Error( 'Promise test error' ));
        };
}
