// =========================================
// PREFETCH BIND
// =========================================

import {
    config,
} from '../../core/config.js';

import {
    shouldIgnoreLink,
} from '../../core/navigation.js';

import {
    prefetchPage,
} from './prefetch-request.js';

// =========================================
// BIND LINK
// =========================================

function bindLink(
    link,
)
{
    /*
    |--------------------------------------------------------------------------
    | VALID LINK
    |--------------------------------------------------------------------------
    */

    if (
        !(
            link
            instanceof HTMLAnchorElement
        )
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | IGNORE LINK
    |--------------------------------------------------------------------------
    */

    if (
        shouldIgnoreLink(
            link,
        )
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | SENSITIVE LINKS
    |--------------------------------------------------------------------------
    */

    if (
        link.hasAttribute(
            'data-confirm-logout',
        )
        || link.pathname.endsWith(
            '/deconnexion',
        )
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | ALREADY BOUND
    |--------------------------------------------------------------------------
    */

    if (
        link.dataset.prefetchBound
        === 'true'
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | MARK AS BOUND
    |--------------------------------------------------------------------------
    */

    link.dataset.prefetchBound =
        'true';

    let hoverTimer =
        null;

    /*
    |--------------------------------------------------------------------------
    | HOVER PREFETCH
    |--------------------------------------------------------------------------
    */

    link.addEventListener(
        'pointerenter',
        () =>
        {
            clearTimeout(
                hoverTimer,
            );

            hoverTimer =
                window.setTimeout(
                    () =>
                    {
                        void prefetchPage(
                            link.href,
                        );
                    },
                    config.prefetch.hoverDelay,
                );
        },
        {
            passive:
                true,
        },
    );

    /*
    |--------------------------------------------------------------------------
    | CANCEL PREFETCH
    |--------------------------------------------------------------------------
    */

    link.addEventListener(
        'pointerleave',
        () =>
        {
            clearTimeout(
                hoverTimer,
            );
        },
        {
            passive:
                true,
        },
    );
}

// =========================================
// BIND PREFETCH
// =========================================

export function bindPrefetch()
{
    const links =
        document.querySelectorAll(
            'a[data-prefetch]',
        );

    for (
        const link
        of links
    )
    {
        bindLink(
            link,
        );
    }
}