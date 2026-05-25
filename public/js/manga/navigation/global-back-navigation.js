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

function isInteractiveElement(
    target,
)
{
    if (!target) {
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

/*
|------------------------------------------------------------------
| Navigation
|------------------------------------------------------------------
*/

function navigateBack()
{
    if (
        window.history.length > 1
    ) {

        window.history.back();

        return;
    }

    window.location.href =
        '/lolissr/';
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
    | Ignore interactive
    |--------------------------------------------------------------
    */

    if (
        isInteractiveElement(
            event.target,
        )
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Ignore modifiers
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