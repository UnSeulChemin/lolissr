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

    link.addEventListener(
        'pointerenter',
        (
            event,
        ) =>
        {
            if (
                !event.isTrusted
            ) {

                return;
            }

            void prefetchPage(
                link.href,
            );
        },
        {
            passive:
                true,
        },
    );

    link.addEventListener(
        'pointerdown',
        () =>
        {
            void prefetchPage(
                link.href,
            );
        },
        {
            passive:
                true,
        },
    );

    link.addEventListener(
        'touchstart',
        () =>
        {
            void prefetchPage(
                link.href,
            );
        },
        {
            passive:
                true,

            once:
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