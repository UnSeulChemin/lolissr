// =========================================
// GLOBAL BACK NAVIGATION
// =========================================

import {
    config,
} from '../core/config.js';

import {
    navigateTo,
} from '../router/router.js';

import {
    debug,
} from '../core/debug.js';

// =========================================
// SELECTORS
// =========================================

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
// UNLOCK
// =========================================

function unlockNextFrame()
{
    requestAnimationFrame(
        () =>
        {
            locked =
                false;

            debug(
                'BACKSPACE',
                'unlock',
            );
        },
    );
}

// =========================================
// BACK
// =========================================

function navigateBack()
{
    debug(
        'BACKSPACE',
        'navigate-back',
        {
            locked,
            href:
                location.href,

            history:
                window.history.length,
        },
    );

    // =====================================
    // LOCK
    // =====================================

    if (locked) {

        debug(
            'BACKSPACE',
            'blocked',
        );

        return;
    }

    locked =
        true;

    debug(
        'BACKSPACE',
        'lock',
    );

    // =====================================
    // HISTORY
    // =====================================

    if (
        window.history.length > 1
    ) {

        debug(
            'BACKSPACE',
            'history-back',
        );

        window.history.back();

        unlockNextFrame();

        return;
    }

    // =====================================
    // FALLBACK
    // =====================================

    debug(
        'BACKSPACE',
        'fallback',
        config.baseUrl,
    );

    void navigateTo(
        config.baseUrl,
    );

    unlockNextFrame();
}

// =========================================
// KEYBOARD
// =========================================

function handleKeyboard(
    event,
)
{
    // =====================================
    // KEY
    // =====================================

    if (
        event.key
        !== 'Backspace'
    ) {
        return;
    }

    debug(
        'BACKSPACE',
        'keydown',
        {
            repeat:
                event.repeat,

            ctrl:
                event.ctrlKey,

            shift:
                event.shiftKey,

            meta:
                event.metaKey,

            alt:
                event.altKey,
        },
    );

    // =====================================
    // REPEAT
    // =====================================

    if (
        event.repeat
    ) {

        debug(
            'BACKSPACE',
            'blocked-repeat',
        );

        return;
    }

    // =====================================
    // MODIFIERS
    // =====================================

    if (
        event.ctrlKey
        || event.metaKey
        || event.altKey
        || event.shiftKey
    ) {

        debug(
            'BACKSPACE',
            'blocked-modifier',
        );

        return;
    }

    // =====================================
    // INPUT
    // =====================================

    if (
        isTypingContext(
            event.target,
        )
    ) {

        debug(
            'BACKSPACE',
            'blocked-input',
        );

        return;
    }

    // =====================================
    // INTERACTIVE
    // =====================================

    if (
        isInteractiveElement(
            event.target,
        )
    ) {

        debug(
            'BACKSPACE',
            'blocked-interactive',
        );

        return;
    }

    // =====================================
    // ACTIVE CARD
    // =====================================

    if (
        document.querySelector(
            '.collection-card-link.is-active',
        )
    ) {

        debug(
            'BACKSPACE',
            'clear-active-card',
        );

        return;
    }

    // =====================================
    // PREVENT
    // =====================================

    event.preventDefault();

    debug(
        'BACKSPACE',
        'prevent-default',
    );

    // =====================================
    // NAVIGATION
    // =====================================

    navigateBack();
}

// =========================================
// INIT
// =========================================

export function initGlobalBackNavigation()
{
    if (initialized) {

        debug(
            'BACKSPACE',
            'already-init',
        );

        return;
    }

    initialized =
        true;

    debug(
        'BACKSPACE',
        'init',
    );

    document.addEventListener(
        'keydown',
        handleKeyboard,
        {
            passive:
                false,
        },
    );
}