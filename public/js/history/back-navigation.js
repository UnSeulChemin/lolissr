// =========================================
// GLOBAL BACK NAVIGATION
// =========================================

import {
    config,
} from '../core/config.js';

import {
    navigateTo,
} from '../router/router-navigation.js';

import {
    debug,
} from '../core/debug/debug.js';

// =========================================
// SELECTORS
// =========================================

const TYPING_SELECTOR =
`
input,
textarea,
select,
[contenteditable="true"]
`;

const INTERACTIVE_SELECTOR =
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
        TYPING_SELECTOR,
    );
}

function isInteractiveElement(
    target,
)
{
    return hasClosest(
        target,
        INTERACTIVE_SELECTOR,
    );
}

// =========================================
// LOCK
// =========================================

function unlock()
{
    locked =
        false;
}

function lock()
{
    locked =
        true;
}

// =========================================
// BACK
// =========================================

function navigateBack()
{
    /*
    |--------------------------------------------------------------------------
    | LOCK
    |--------------------------------------------------------------------------
    */

    if (locked) {

        debug(
            'BACKSPACE',
            'blocked',
        );

        return;
    }

    lock();

    debug(
        'BACKSPACE',
        'navigate',
        location.pathname,
    );

    /*
    |--------------------------------------------------------------------------
    | HISTORY BACK
    |--------------------------------------------------------------------------
    */

    if (
        window.history.length > 1
    ) {

        window.history.back();

        requestAnimationFrame(
            unlock,
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | FALLBACK
    |--------------------------------------------------------------------------
    */

    void navigateTo(
        config.baseUri,
    ).finally(
        unlock,
    );
}

// =========================================
// KEYBOARD
// =========================================

function handleKeyboard(
    event,
)
{
    /*
    |--------------------------------------------------------------------------
    | KEY
    |--------------------------------------------------------------------------
    */

    if (
        event.key
        !== 'Backspace'
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | REPEAT
    |--------------------------------------------------------------------------
    */

    if (
        event.repeat
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | MODIFIERS
    |--------------------------------------------------------------------------
    */

    if (
        event.ctrlKey
        || event.metaKey
        || event.altKey
        || event.shiftKey
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | INPUT
    |--------------------------------------------------------------------------
    */

    if (
        isTypingContext(
            event.target,
        )
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | INTERACTIVE
    |--------------------------------------------------------------------------
    */

    if (
        isInteractiveElement(
            event.target,
        )
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | PREVENT DEFAULT
    |--------------------------------------------------------------------------
    */

    event.preventDefault();

    /*
    |--------------------------------------------------------------------------
    | NAVIGATION
    |--------------------------------------------------------------------------
    */

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

    document.addEventListener(
        'keydown',
        handleKeyboard,
        {
            passive:
                false,
        },
    );

    debug(
        'BACKSPACE',
        'ready',
    );
}