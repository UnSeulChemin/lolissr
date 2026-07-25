// =========================================
// NAVIGATION RENDER
// =========================================

import {
    cachePage,
} from '../page-cache.js';

import {
    updateActiveNavigation,
} from '../router-active-link.js';

import {
    replaceContent,
} from '../router-dom.js';

import {
    clearActiveFocus,
} from '../router-focus.js';

import {
    restoreScrollPosition,
} from '../route-scroll.js';

// =========================================
// RENDER
// =========================================

export async function renderPage(
    current,
    target,
    response,
    options,
)
{
    if (
        options.updateHistory !== false
    )
    {
        history.pushState(
            {},
            '',
            target,
        );
    }

    if (
        typeof response.page.title === 'string'
    )
    {
        document.title =
            response.page.title;
    }

    cachePage(
        target,
        response,
    );

    replaceContent(
        response.page.html,
    );

    updateActiveNavigation();

    clearActiveFocus();

    /*
    |--------------------------------------------------------------------------
    | HASH SCROLL
    |--------------------------------------------------------------------------
    */

    if (
        window.location.hash
    )
    {
        queueMicrotask(
            () =>
            {
                document
                    .querySelector(
                        window.location.hash,
                    )
                    ?.scrollIntoView();
            },
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE SCROLL
    |--------------------------------------------------------------------------
    */

    if (
        options.updateHistory === false
    )
    {
        restoreScrollPosition(
            target,
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | DEFAULT SCROLL
    |--------------------------------------------------------------------------
    */

    window.scrollTo(
        0,
        0,
    );
}