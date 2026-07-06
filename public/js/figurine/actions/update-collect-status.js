// =========================================
// UPDATE COLLECT STATUS
// =========================================

import {
    post,
} from '../../core/http.js';

import {
    $$,
    delegate,
} from '../../core/dom.js';

import {
    showToast,
} from '../../core/toast.js';

import {
    debug,
} from '../../core/debug/debug.js';

import {
    handleError,
} from '../../core/errors/error-handler.js';

import {
    FrontendError,
} from '../../core/errors/FrontendError.js';

import {
    invalidateFigurinePages,
} from '../figurine-cache.js';

import {
    updateHeaderUser,
} from '../../profil/header-user.js';

// =========================================
// CONFIG
// =========================================

const BUTTON_SELECTOR =
    '.js-collect-status-button';

// =========================================
// STATE
// =========================================

let initialized =
    false;

// =========================================
// HELPERS
// =========================================

function updateCollectButtonState(
    button,
    collectStatus,
)
{
    const isCollected =
        Number(
            collectStatus,
        ) === 1;

    button.dataset.collectStatus =
        String(
            collectStatus,
        );

    button.classList.toggle(
        'active',
        isCollected,
    );

    const label =
        isCollected
            ? 'Retirer de la collection'
            : 'Ajouter à la collection';

    button.title =
        label;

    button.setAttribute(
        'aria-label',
        label,
    );

    button.setAttribute(
        'aria-pressed',
        isCollected
            ? 'true'
            : 'false',
    );
}

function refreshButtons()
{
    $$(
        BUTTON_SELECTOR,
    ).forEach(
        (
            button,
        ) =>
        {
            if (
                !(
                    button
                    instanceof HTMLButtonElement
                )
            ) {

                return;
            }

            updateCollectButtonState(
                button,
                Number(
                    button.dataset.collectStatus
                    ?? 0,
                ),
            );
        },
    );
}

// =========================================
// UPDATE
// =========================================

async function updateCollectStatus(
    button,
)
{
    if (
        button.disabled
    ) {

        return;
    }

    const url =
        button.dataset.url;

    if (!url) {

        return;
    }

    const currentCollectStatus =
        Number(
            button.dataset.collectStatus
            ?? 0,
        );

    const nextCollectStatus =
        currentCollectStatus === 1
            ? 0
            : 1;

    /*
    |--------------------------------------------------------------------------
    | OPTIMISTIC UI
    |--------------------------------------------------------------------------
    */

    button.disabled =
        true;

    updateCollectButtonState(
        button,
        nextCollectStatus,
    );

    try {

        const data =
            await post(
                url,
                {
                    collectStatus:
                        nextCollectStatus,
                },
                {
                    headers:
                    {
                        Accept:
                            'application/json',
                    },
                },
            );

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            data?.success
            !== true
        ) {

            throw new FrontendError(
                data?.message
                || 'Erreur mise à jour',
                {
                    code:
                        'COLLECT_STATUS_UPDATE_FAILED',
                },
            );
        }

        /*
        |--------------------------------------------------------------------------
        | APPLY SERVER STATE
        |--------------------------------------------------------------------------
        */

        const collectStatus =
            Number(
                data?.data?.collectStatus
                ?? nextCollectStatus,
            );

        updateCollectButtonState(
            button,
            collectStatus,
        );

        updateHeaderUser(
            data?.data?.level,
        );

        invalidateFigurinePages();

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        let message =
            data?.message
            || 'Mise à jour effectuée';

        if (data?.data?.xpEarned)
        {
            message += ' ⭐ +50 XP';
        }

        showToast(
            message,
            'success',
        );

    } catch (error) {

        /*
        |--------------------------------------------------------------------------
        | RESTORE PREVIOUS STATE
        |--------------------------------------------------------------------------
        */

        updateCollectButtonState(
            button,
            currentCollectStatus,
        );

        handleError(
            error,
        );

    } finally {

        button.disabled =
            false;
    }
}

// =========================================
// INIT
// =========================================

export function initUpdateCollectStatus()
{
    if (initialized) {

        return;
    }

    initialized =
        true;

    delegate(
        document,
        'click',
        BUTTON_SELECTOR,
        (
            _,
            button,
        ) =>
        {
            if (
                !(
                    button
                    instanceof HTMLButtonElement
                )
            ) {

                return;
            }

            void updateCollectStatus(
                button,
            );
        },
    );

    document.addEventListener(
        'router:loaded',
        refreshButtons,
        {
            passive:
                true,
        },
    );

    refreshButtons();

    debug(
        'COLLECT_STATUS',
        'initialized',
    );
}