// ==================================================
// Global Prefetch Navigation
// ==================================================

/*
|------------------------------------------------------------------
| Cache
|------------------------------------------------------------------
*/

const prefetchedPages =
    new Map();

const pendingRequests =
    new Set();

const recentPrefetches =
    new Map();

/*
|------------------------------------------------------------------
| State
|------------------------------------------------------------------
*/

let initialized =
    false;

/*
|------------------------------------------------------------------
| Selectors
|------------------------------------------------------------------
*/

const linkSelector =
    `
    a.card-link,
    a.dashboard-card,
    a.nav-link-icon,
    a.collection-pagination-link
    `;

/*
|------------------------------------------------------------------
| Helpers
|------------------------------------------------------------------
*/

function normalizeUrl(
    href,
)
{
    const url =
        new URL(
            href,
            window.location.origin,
        );

    return (
        url.pathname
        + url.search
    );
}

export function getPrefetchedPage(
    href,
)
{
    const normalizedUrl =
        normalizeUrl(
            href,
        );

    return (
        prefetchedPages.get(
            normalizedUrl,
        )
        || null
    );
}

function storePrefetchedPage(
    url,
    html,
)
{
    prefetchedPages.set(
        url,
        html,
    );
}

function shouldIgnoreLink(
    link,
)
{
    if (
        !(link instanceof HTMLAnchorElement)
    ) {
        return true;
    }

    if (!link.href) {
        return true;
    }

    const url =
        new URL(
            link.href,
            window.location.origin,
        );

    /*
    |--------------------------------------------------------------
    | External
    |--------------------------------------------------------------
    */

    if (
        url.origin
        !== window.location.origin
    ) {
        return true;
    }

    /*
    |--------------------------------------------------------------
    | Same page hash
    |--------------------------------------------------------------
    */

    if (
        url.hash
        && url.pathname
            === window.location.pathname
    ) {
        return true;
    }

    /*
    |--------------------------------------------------------------
    | Blank target
    |--------------------------------------------------------------
    */

    if (
        link.target
        === '_blank'
    ) {
        return true;
    }

    /*
    |--------------------------------------------------------------
    | Download
    |--------------------------------------------------------------
    */

    if (
        link.hasAttribute(
            'download',
        )
    ) {
        return true;
    }

    /*
    |--------------------------------------------------------------
    | Static files
    |--------------------------------------------------------------
    */

    if (
        /\.(jpg|jpeg|png|gif|webp|svg|pdf|zip)$/i
            .test(url.pathname)
    ) {
        return true;
    }

    return false;
}

/*
|------------------------------------------------------------------
| Prefetch
|------------------------------------------------------------------
*/

export async function prefetchPage(
    href,
)
{
    try {

        const normalizedUrl =
            normalizeUrl(
                href,
            );

        /*
        |----------------------------------------------------------
        | Anti spam
        |----------------------------------------------------------
        */

        const now =
            Date.now();

        const lastPrefetch =
            recentPrefetches.get(
                normalizedUrl,
            );

        if (
            lastPrefetch
            && now - lastPrefetch < 3000
        ) {
            return;
        }

        recentPrefetches.set(
            normalizedUrl,
            now,
        );

        /*
        |----------------------------------------------------------
        | Already cached
        |----------------------------------------------------------
        */

        if (
            prefetchedPages.has(
                normalizedUrl,
            )
        ) {
            return;
        }

        /*
        |----------------------------------------------------------
        | Already pending
        |----------------------------------------------------------
        */

        if (
            pendingRequests.has(
                normalizedUrl,
            )
        ) {
            return;
        }

        pendingRequests.add(
            normalizedUrl,
        );

        const response =
            await fetch(
                normalizedUrl,
                {
                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                },
            );

        pendingRequests.delete(
            normalizedUrl,
        );

        if (!response.ok) {
            return;
        }

        const html =
            await response.text();

        storePrefetchedPage(
            normalizedUrl,
            html,
        );

    } catch (error) {

        console.error(
            '[PREFETCH]',
            error,
        );
    }
}

/*
|------------------------------------------------------------------
| Hover / Focus Prefetch
|------------------------------------------------------------------
*/

function bindHoverPrefetch()
{
    const links =
        document.querySelectorAll(
            linkSelector,
        );

    links.forEach(
        link =>
        {
            if (
                shouldIgnoreLink(
                    link,
                )
            ) {
                return;
            }

            /*
            |------------------------------------------------------
            | Prevent duplicate binding
            |------------------------------------------------------
            */

            if (
                link.dataset.prefetchBound
                === 'true'
            ) {
                return;
            }

            link.dataset.prefetchBound =
                'true';

            let hoverTimeout =
                null;

            /*
            |------------------------------------------------------
            | Hover
            |------------------------------------------------------
            */

            link.addEventListener(
                'mouseenter',
                () =>
                {
                    hoverTimeout =
                        window.setTimeout(
                            () =>
                            {
                                prefetchPage(
                                    link.href,
                                );
                            },
                            80,
                        );
                },
                {
                    passive: true,
                },
            );

            /*
            |------------------------------------------------------
            | Cancel hover
            |------------------------------------------------------
            */

            link.addEventListener(
                'mouseleave',
                () =>
                {
                    clearTimeout(
                        hoverTimeout,
                    );
                },
                {
                    passive: true,
                },
            );

            /*
            |------------------------------------------------------
            | Keyboard focus
            |------------------------------------------------------
            */

            link.addEventListener(
                'focus',
                () =>
                {
                    prefetchPage(
                        link.href,
                    );
                },
                {
                    passive: true,
                },
            );

            /*
            |------------------------------------------------------
            | Mobile touch
            |------------------------------------------------------
            */

            link.addEventListener(
                'touchstart',
                () =>
                {
                    prefetchPage(
                        link.href,
                    );
                },
                {
                    passive: true,
                    once: true,
                },
            );
        },
    );
}

/*
|------------------------------------------------------------------
| Auto preload next pagination page
|------------------------------------------------------------------
*/

function prefetchNextSeriesPage()
{
    const nextPage =
        document.querySelector(
            '.collection-pagination-link.active + .collection-pagination-link',
        );

    if (
        !nextPage
        || !(nextPage instanceof HTMLAnchorElement)
    ) {
        return;
    }

    prefetchPage(
        nextPage.href,
    );
}

/*
|------------------------------------------------------------------
| Init
|------------------------------------------------------------------
*/

export function initPrefetchNavigation()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    /*
    |--------------------------------------------------------------
    | Hover / Focus prefetch
    |--------------------------------------------------------------
    */

    bindHoverPrefetch();

    /*
    |--------------------------------------------------------------
    | Auto preload next pagination page
    |--------------------------------------------------------------
    */

    prefetchNextSeriesPage();

    /*
    |--------------------------------------------------------------
    | Rebind after AJAX
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'ajax:series-loaded',
        () =>
        {
            bindHoverPrefetch();

            prefetchNextSeriesPage();
        },
    );
}