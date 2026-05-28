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
// BIND LINK
// =========================================

function bindLink(
    link,
)
{
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

    if (
        link.dataset.prefetchBound
        === 'true'
    ) {

        return;
    }

    link.dataset.prefetchBound =
        'true';

    let hoverTimer =
        null;

    link.addEventListener(
        'mouseenter',
        () =>
        {
            hoverTimer =
                window.setTimeout(
                    () =>
                    {
                        void prefetchPage(
                            link.href,
                        );
                    },
                    80,
                );
        },
        {
            passive:
                true,
        },
    );

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