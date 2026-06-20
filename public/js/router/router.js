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
} from '../core/debug/debug.js';

import {
    confirmModal,
} from '../core/modal/confirm-modal.js';

import {
    request,
} from '../core/http.js';

// =========================================
// CLICK
// =========================================

async function handleClick(
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
        link.hasAttribute(
            'data-confirm-logout',
        )
    ) {

        event.preventDefault();

        const confirmed =
            await confirmModal(
                {
                    title:
                        'Déconnexion',

                    message:
                        'Êtes-vous sûr de vouloir vous déconnecter ?',

                    confirmText:
                        'Déconnexion',
                },
            );

        if (
            ! confirmed
        ) {

            return;
        }

        const response =
            await request(
                link.href,
                {
                    method:
                        'POST',
                },
            );

        if (
            response?.type
            === 'redirect'
        ) {

            window.location.href =
                response.redirect;
        }

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
        link.href,
    );
}

// =========================================
// POPSTATE
// =========================================

async function handlePopState()
{
    document.body.classList.add(
        'no-route-animation',
    );

    await navigateTo(
        location.href,
        {
            updateHistory: false,
            force: true,
        },
    );

    requestAnimationFrame(
        () =>
        {
            document.body.classList.remove(
                'no-route-animation',
            );
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