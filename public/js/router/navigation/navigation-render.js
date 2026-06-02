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

    window.scrollTo(
        0,
        0,
    );
}