// =========================================
// ROUTER
// =========================================

import {
    shouldIgnoreLink,
} from '../core/navigation.js';

import {
    navigateTo,
} from './router-navigation.js';

import {
    updateActiveNavigation,
} from './router-active-link.js';

import {
    clearActiveFocus,
} from './router-focus.js';

import {
    debug,
} from '../core/debug.js';

// =========================================
// CLICK
// =========================================

function handleClick(
    event,
)
{
    if (
        event.defaultPrevented
    ) {

        return;
    }

    if (
        event.button !== 0
    ) {

        return;
    }

    if (
        event.ctrlKey
        || event.metaKey
        || event.shiftKey
        || event.altKey
    ) {

        return;
    }

    const target =
        event.target;

    if (
        !(
            target
            instanceof Element
        )
    ) {

        return;
    }

    const link =
        target.closest(
            'a[href]',
        );

    if (
        !(
            link
            instanceof HTMLAnchorElement
        )
    ) {

        return;
    }

    if (
        shouldIgnoreLink(
            link,
        )
    ) {

        return;
    }

    event.preventDefault();

    clearActiveFocus();

    void navigateTo(
        location.href,
        link.href,
    );
}

// =========================================
// POPSTATE
// =========================================

async function handlePopState()
{
    await navigateTo(
        null,
        location.href,
        {
            updateHistory:
                false,

            force:
                true,
        },
    );
}

// =========================================
// INIT
// =========================================

export function initRouter()
{
    document.addEventListener(
        'click',
        handleClick,
    );

    window.addEventListener(
        'popstate',
        handlePopState,
    );

    updateActiveNavigation();

    debug(
        'ROUTER',
        'ready',
    );
}