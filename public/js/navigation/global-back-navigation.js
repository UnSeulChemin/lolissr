// =========================================
// GLOBAL BACK NAVIGATION
// =========================================

import {
    config,
} from '../core/config.js';

// =========================================
// Config
// =========================================

const BACK_LOCK_DURATION =
    config.navigation
        .backLockDuration;

const typingSelector =
`
input,
textarea,
select,
[contenteditable="true"]
`;

const interactiveSelector =
`
a,
button,
[role="button"]
`;

// =========================================
// State
// =========================================

let initialized =
    false;

let locked =
    false;

// =========================================
// Helpers
// =========================================

function hasClosest(
    target,
    selector,
)
{
    return (
        target instanceof Element
        && Boolean(
            target.closest(
                selector,
            ),
        )
    );
}

function isTypingContext(
    target,
)
{
    return hasClosest(
        target,
        typingSelector,
    );
}

function isInteractiveElement(
    target,
)
{
    return hasClosest(
        target,
        interactiveSelector,
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

// =========================================
// Navigation
// =========================================

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

    // =====================================
    // Hard fallback
    // =====================================

    window.location.assign(
        config.baseUrl,
    );
}

// =========================================
// Keyboard
// =========================================

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

    // Ignore modifiers

    if (
        event.ctrlKey
        || event.metaKey
        || event.altKey
        || event.shiftKey
    ) {
        return;
    }

    // Ignore forms

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

    event.preventDefault();

    navigateBack();
}

// =========================================
// Init
// =========================================

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