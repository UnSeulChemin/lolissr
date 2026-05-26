// ==================================================
// Global Back Navigation
// ==================================================

let initialized =
    false;

let locked =
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
    if (locked) {
        return;
    }

    locked = true;

    if (
        window.history.length > 1
    ) {

        window.history.back();

    } else {

        window.location.href =
            '/lolissr/';
    }

    window.setTimeout(
        () =>
        {
            locked = false;
        },
        350,
    );
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

    // Prevent key repeat spam

    if (
        event.repeat
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