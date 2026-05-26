// =========================================
// TOAST
// =========================================

import {
    $,
} from './dom.js';

import {
    debug,
} from './debug.js';

import {
    config,
} from './config.js';

// =========================================
// Config
// =========================================

const TOAST_DURATION =
    config.toast?.duration
    ?? 2400;

// =========================================
// State
// =========================================

let toastHideTimeout =
    null;

// =========================================
// Constants
// =========================================

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

// =========================================
// Helpers
// =========================================

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

// =========================================
// Public API
// =========================================

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

    // =====================================
    // Reset
    // =====================================

    clearHideTimeout();

    clearToastState(
        toastElement,
    );

    // =====================================
    // Type
    // =====================================

    toastElement.classList.add(
        getToastClass(
            type,
        ),
    );

    // =====================================
    // Content
    // =====================================

    renderToastContent(
        toastElement,
        message,
        type,
    );

    // =====================================
    // Restart Animation
    // =====================================

    restartAnimation(
        toastElement,
    );

    // =====================================
    // Show
    // =====================================

    toastElement.classList.add(
        'show',
    );

    // =====================================
    // Auto Hide
    // =====================================

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