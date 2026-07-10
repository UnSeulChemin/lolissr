// =========================================
// UPDATE READ STATUS
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
    invalidateMangaPages,
} from '../manga-cache.js';

import {
    updateHeaderUser,
} from '../../profil/header-user.js';

// =========================================
// CONFIG
// =========================================

const BUTTON_SELECTOR =
    '.js-read-status-button';

// =========================================
// STATE
// =========================================

let initialized =
    false;

// =========================================
// HELPERS
// =========================================

function updateButtonState(
    button,
    readStatus,
)
{
    const isRead =
        Number(
            readStatus,
        ) === 1;

    button.dataset.readStatus =
        String(
            readStatus,
        );

    button.classList.toggle(
        'active',
        isRead,
    );

    const label =
        isRead
            ? 'Marquer comme non lu'
            : 'Marquer comme lu';

    button.title =
        label;

    button.setAttribute(
        'aria-label',
        label,
    );

    button.setAttribute(
        'aria-pressed',
        isRead
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

            updateButtonState(
                button,
                Number(
                    button.dataset.readStatus
                    ?? 0,
                ),
            );
        },
    );
}

// =========================================
// UPDATE
// =========================================

async function updateReadStatus(
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

    const currentReadStatus =
        Number(
            button.dataset.readStatus
            ?? 0,
        );

    const nextReadStatus =
        currentReadStatus === 1
            ? 0
            : 1;

    /*
    |--------------------------------------------------------------------------
    | OPTIMISTIC UI
    |--------------------------------------------------------------------------
    */

    button.disabled =
        true;

    updateButtonState(
        button,
        nextReadStatus,
    );

    try {

        const data =
            await post(
                url,
                {
                    readStatus:
                        nextReadStatus,
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
                        'READ_STATUS_UPDATE_FAILED',
                },
            );
        }

        /*
        |--------------------------------------------------------------------------
        | APPLY SERVER STATE
        |--------------------------------------------------------------------------
        */

        const readStatus =
            Number(
                data?.data?.readStatus
                ?? nextReadStatus,
            );

        updateButtonState(
            button,
            readStatus,
        );

        updateHeaderUser(
            data?.data?.level,
        );

        invalidateMangaPages();

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
            const xpAmount =
                Number(
                    data?.data?.xpAmount
                    ?? 0,
                );

            message +=
                ` ⭐ +${xpAmount} XP`;
        }

        if (data?.data?.seriesXpEarned)
        {
            message += ' 📚 +20 XP';
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

        updateButtonState(
            button,
            currentReadStatus,
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

export function initUpdateReadStatus()
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

            void updateReadStatus(
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
        'READ_STATUS',
        'initialized',
    );
}