// =========================================
// NAVIGATION RENDER
// =========================================

import {
    replaceContent,
} from '../router-dom.js';

import {
    updateActiveNavigation,
} from '../router-active-link.js';

import {
    clearActiveFocus,
} from '../router-focus.js';

import {
    restoreScrollPosition,
} from '../route-scroll.js';

import {
    emitNavigationRender,
} from './navigation-events.js';

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
        options.updateHistory
        !== false
    ) {

        history.pushState(
            {},
            '',
            target,
        );
    }

    if (
        typeof response.page.title
        === 'string'
    ) {

        document.title =
            response.page.title;
    }

    emitNavigationRender(
        current,
        target,
    );

    replaceContent(
        response.page.html,
    );

    updateActiveNavigation();

    clearActiveFocus();

    // =====================================
    // HASH SCROLL
    // =====================================

    if (
        window.location.hash
    ) {

        setTimeout(
            () =>
            {
                document
                    .querySelector(
                        window.location.hash,
                    )
                    ?.scrollIntoView();
            },
            0,
        );

        return;
    }

    // =====================================
    // RESTORE SCROLL (BACK/FORWARD)
    // =====================================

    if (
        options.updateHistory
        === false
    ) {

        restoreScrollPosition(
            target,
        );

        return;
    }

    // =====================================
    // DEFAULT SCROLL
    // =====================================

    window.scrollTo(
        0,
        0,
    );
}