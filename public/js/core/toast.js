// =================================================
// NOTIFICATION
// =================================================

import {
    $,
} from './dom.js';

import {
    debug,
} from './debug/debug.js';

import {
    config,
} from './config.js';

// =================================================
// CONFIGURATION
// =================================================

const TOAST_DURATION =
    config.toast?.duration
    ?? 2400;

// =================================================
// ÉTAT
// =================================================

let toastHideTimeout =
    null;

// =================================================
// CONSTANTES
// =================================================

const toastIcons =
{
    success:
        '✓',

    error:
        '✕',

    info:
        '✦',
};

const toastClasses =
{
    success:
        'toast-success',

    error:
        'toast-error',

    info:
        'toast-info',
};

// =================================================
// UTILITAIRES
// =================================================

function escapeHtml(
    value,
)
{
    return String(
        value ?? '',
    )
        .replaceAll(
            '&',
            '&amp;',
        )
        .replaceAll(
            '<',
            '&lt;',
        )
        .replaceAll(
            '>',
            '&gt;',
        )
        .replaceAll(
            '"',
            '&quot;',
        )
        .replaceAll(
            "'",
            '&#039;',
        );
}

function getToastElement()
{
    return $(
        '#toast',
    );
}

function getToastIcon(
    type,
)
{
    return (
        toastIcons[type]
        || toastIcons.success
    );
}

function getToastClass(
    type,
)
{
    return (
        toastClasses[type]
        || toastClasses.success
    );
}

function clearToastState(
    toastElement,
)
{
    toastElement.classList.remove(
        'toast-success',
        'toast-error',
        'toast-info',
        'show',
    );
}

function renderToastContent(
    toastElement,
    message,
    type,
)
{
    toastElement.innerHTML =
    `
        <span class="toast-wing toast-wing-left"></span>

        <div class="toast-content">

            <span class="toast-icon">
                ${getToastIcon(type)}
            </span>

            <span class="toast-message">
                ${escapeHtml(message)}
            </span>

        </div>

        <span class="toast-wing toast-wing-right"></span>

        <span class="toast-shine"></span>
    `;
}

function restartAnimation(
    toastElement,
)
{
    void toastElement.offsetWidth;
}

function clearHideTimeout()
{
    if (
        !toastHideTimeout
    ) {
        return;
    }

    clearTimeout(
        toastHideTimeout,
    );

    toastHideTimeout =
        null;
}

// =================================================
// API PUBLIQUE
// =================================================

export function showToast(
    message =
        'Sauvegardé',
    type =
        'success',
)
{
    const toastElement =
        getToastElement();

    if (!toastElement) {

        debug(
            'TOAST',
            '#toast introuvable',
        );

        return;
    }

    debug(
        'TOAST',
        type,
        message,
    );

    // =================================================
    // RÉINITIALISATION
    // =================================================

    clearHideTimeout();

    clearToastState(
        toastElement,
    );

    // =================================================
    // TYPE
    // =================================================

    toastElement.classList.add(
        getToastClass(
            type,
        ),
    );

    // =================================================
    // CONTENU
    // =================================================

    renderToastContent(
        toastElement,
        message,
        type,
    );

    // =================================================
    // REDÉMARRAGE
    // =================================================

    restartAnimation(
        toastElement,
    );

    // =================================================
    // AFFICHAGE
    // =================================================

    toastElement.classList.add(
        'show',
    );

    // =================================================
    // MASQUAGE AUTOMATIQUE
    // =================================================

    toastHideTimeout =
        window.setTimeout(
            () =>
            {
                toastElement.classList.remove(
                    'show',
                );
            },
            TOAST_DURATION,
        );
}

// =================================================
// NOTIFICATION DE SESSION
// =================================================

export function initFlashToast()
{
    const flashToast = window.flashToast;
    delete window.flashToast;
    if (flashToast?.message)
    {
        showToast(flashToast.message, flashToast.type ?? 'success');
    }
}
