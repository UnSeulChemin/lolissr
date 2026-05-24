// ==================================================
// Global Back Navigation
// ==================================================

let initialized = false;

/*
|------------------------------------------------------------------
| Helpers
|------------------------------------------------------------------
*/

function isTypingContext(target)
{
    if (!target) {
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
    window.history.back();
}

/*
|------------------------------------------------------------------
| Keyboard
|------------------------------------------------------------------
*/

function handleKeyboard(event)
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