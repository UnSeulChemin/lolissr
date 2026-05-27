// =========================================
// GLOBAL BACK NAVIGATION
// =========================================

import {
    config,
} from '../core/config.js';

import {
    navigateTo,
} from './ajax-navigation.js';

// =========================================
// CONFIG
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
// BACK
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

    void navigateTo(
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
    if (
        event.key
        !== 'Backspace'
    ) {
        return;
    }

    if (
        event.repeat
    ) {
        return;
    }

    if (
        event.ctrlKey
        || event.metaKey
        || event.altKey
        || event.shiftKey
    ) {
        return;
    }

    if (
        isTypingContext(
            event.target,
        )
    ) {
        return;
    }

    if (
        isInteractiveElement(
            event.target,
        )
    ) {
        return;
    }

    if (
        document.querySelector(
            '.collection-card-link.is-active',
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
        {
            passive:
                false,
        },
    );
}