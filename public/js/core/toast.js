// ==================================================
// Toast
// ==================================================

import {
    debug,
} from './debug.js';

// ==================================================
// Config
// ==================================================

const TOAST_DURATION =
    2400;

// ==================================================
// State
// ==================================================

let toastHideTimeout =
    null;

// ==================================================
// Helpers
// ==================================================

function getToastElement()
{
    return document.getElementById(
        'toast',
    );
}

function getToastIcon(
    type,
)
{
    switch (type) {

        case 'error':
            return '✕';

        case 'info':
            return '✦';

        default:
            return '✓';
    }
}

function getToastClass(
    type,
)
{
    switch (type) {

        case 'error':
            return 'toast-error';

        case 'info':
            return 'toast-info';

        default:
            return 'toast-success';
    }
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
                ${message}
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
        toastHideTimeout
    ) {

        clearTimeout(
            toastHideTimeout,
        );

        toastHideTimeout =
            null;
    }
}

// ==================================================
// Public API
// ==================================================

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

    // ==============================================
    // Reset
    // ==============================================

    clearHideTimeout();

    clearToastState(
        toastElement,
    );

    // ==============================================
    // Type
    // ==============================================

    toastElement.classList.add(
        getToastClass(
            type,
        ),
    );

    // ==============================================
    // Content
    // ==============================================

    renderToastContent(
        toastElement,
        message,
        type,
    );

    // ==============================================
    // Restart animation
    // ==============================================

    restartAnimation(
        toastElement,
    );

    // ==============================================
    // Show
    // ==============================================

    toastElement.classList.add(
        'show',
    );

    // ==============================================
    // Auto hide
    // ==============================================

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