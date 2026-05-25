// ==================================================
// Global Back Navigation
// ==================================================

let initialized =
    false;

/*
|------------------------------------------------------------------
| Helpers
|------------------------------------------------------------------
*/

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

/*
|------------------------------------------------------------------
| Navigation
|------------------------------------------------------------------
*/

function navigateBack()
{
    /*
    |--------------------------------------------------------------
    | Prevent empty history issue
    |--------------------------------------------------------------
    */

    if (
        window.history.length <= 1
    ) {
        return;
    }

    window.history.back();
}

/*
|------------------------------------------------------------------
| Keyboard
|------------------------------------------------------------------
*/

function handleKeyboard(
    event,
)
{
    if (
        event.key !== 'Backspace'
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Ignore typing
    |--------------------------------------------------------------
    */

    if (
        isTypingContext(
            event.target,
        )
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Ignore modifier keys
    |--------------------------------------------------------------
    */

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

/*
|------------------------------------------------------------------
| Init
|------------------------------------------------------------------
*/

export function initGlobalBackNavigation()
{
    if (initialized) {
        return;
    }

    initialized = true;

    document.addEventListener(
        'keydown',
        handleKeyboard,
    );
}