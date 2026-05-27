// =========================================
// GLOBAL BACK NAVIGATION
// =========================================

import {
    config,
} from '../core/config.js';

// =========================================
// CONFIG
// =========================================

const BACK_LOCK_DURATION =
    120;

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
// STATE
// =========================================

let initialized =
    false;

let locked =
    false;

let unlockTimer =
    null;

// =========================================
// HELPERS
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

// =========================================
// LOCK
// =========================================

function lockNavigation()
{
    locked =
        true;

    clearTimeout(
        unlockTimer,
    );

    unlockTimer =
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
// NAVIGATION
// =========================================

function navigateBack()
{
    if (locked) {
        return;
    }

    lockNavigation();

    // =====================================
    // HISTORY BACK
    // =====================================

    if (
        window.history.length > 1
    ) {

        window.history.back();

        return;
    }

    // =====================================
    // HARD FALLBACK
    // =====================================

    window.location.assign(
        config.baseUrl,
    );
}

// =========================================
// KEYBOARD
// =========================================

function handleKeyboard(
    event,
)
{
    // =====================================
    // BACKSPACE ONLY
    // =====================================

    if (
        event.key
        !== 'Backspace'
    ) {
        return;
    }

    // =====================================
    // IGNORE REPEAT
    // =====================================

    if (
        event.repeat
    ) {
        return;
    }

    // =====================================
    // IGNORE MODIFIERS
    // =====================================

    if (
        event.ctrlKey
        || event.metaKey
        || event.altKey
        || event.shiftKey
    ) {
        return;
    }

    // =====================================
    // IGNORE INPUTS
    // =====================================

    if (
        isTypingContext(
            event.target,
        )
    ) {
        return;
    }

    // =====================================
    // IGNORE BUTTONS / LINKS
    // =====================================

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
// INIT
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