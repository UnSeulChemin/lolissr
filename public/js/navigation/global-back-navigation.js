// ==================================================
// Global Back Navigation
// ==================================================

import {
    config,
} from '../core/config.js';

// ==================================================
// Config
// ==================================================

const BACK_LOCK_DURATION =
    config.navigation
        .backLockDuration;

// ==================================================
// State
// ==================================================

let initialized =
    false;

let locked =
    false;

// ==================================================
// Helpers
// ==================================================

function isTypingContext(
    target,
)
{
    if (
        !(target instanceof Element)
    ) {
        return false;
    }

    return Boolean(
        target.closest(
            `
            input,
            textarea,
            select,
            [contenteditable="true"]
            `,
        ),
    );
}

function isInteractiveElement(
    target,
)
{
    if (
        !(target instanceof Element)
    ) {
        return false;
    }

    return Boolean(
        target.closest(
            `
            a,
            button,
            [role="button"]
            `,
        ),
    );
}

function lockNavigation()
{
    locked =
        true;

    window.setTimeout(
        () =>
        {
            locked =
                false;
        },
        BACK_LOCK_DURATION,
    );
}

// ==================================================
// Navigation
// ==================================================

function navigateBack()
{
    if (locked) {
        return;
    }

    lockNavigation();

    if (
        window.history.length > 1
    ) {

        window.history.back();

        return;
    }

    // ==============================================
    // Hard fallback
    // ==============================================

    window.location.assign(
        config.baseUrl,
    );
}

// ==================================================
// Keyboard
// ==================================================

function handleKeyboard(
    event,
)
{
    // Backspace only

    if (
        event.key
        !== 'Backspace'
    ) {
        return;
    }

    // Prevent repeat spam

    if (
        event.repeat
    ) {
        return;
    }

    // Ignore typing fields

    if (
        isTypingContext(
            event.target,
        )
    ) {
        return;
    }

    // Ignore buttons / links

    if (
        isInteractiveElement(
            event.target,
        )
    ) {
        return;
    }

    // Ignore modifiers

    if (
        event.ctrlKey
        || event.metaKey
        || event.altKey
        || event.shiftKey
    ) {
        return;
    }

    event.preventDefault();

    navigateBack();
}

// ==================================================
// Init
// ==================================================

export function initGlobalBackNavigation()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    document.addEventListener(
        'keydown',
        handleKeyboard,
    );
}