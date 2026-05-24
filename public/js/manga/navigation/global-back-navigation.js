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
    const pathname =
        window.location.pathname;

    /*
    |--------------------------------------------------------------
    | Manga detail
    |--------------------------------------------------------------
    */

    if (
        /^\/lolissr\/manga\/series\/[^/]+$/.test(
            pathname,
        )
    ) {

        window.location.href =
            '/lolissr/manga/series';

        return;
    }

    /*
    |--------------------------------------------------------------
    | Series list
    |--------------------------------------------------------------
    */

    if (
        pathname ===
        '/lolissr/manga/series'
    ) {

        window.location.href =
            '/lolissr/manga';

        return;
    }

    /*
    |--------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------
    */

    if (
        /^\/lolissr\/manga\/series\/page\/\d+$/.test(
            pathname,
        )
    ) {

        window.location.href =
            '/lolissr/manga';

        return;
    }

    /*
    |--------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------
    */

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

    if (
        isTypingContext(
            event.target,
        )
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