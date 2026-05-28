// =========================================
// PREFETCH BIND
// =========================================

import {
    shouldIgnoreLink,
} from '../../core/navigation.js';

import {
    prefetchPage,
} from './prefetch-request.js';

// =========================================
// CONFIG
// =========================================

const PREFETCH_DELAY =
    80;

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
        'mouseenter',
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
                    PREFETCH_DELAY,
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
        'mouseleave',
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