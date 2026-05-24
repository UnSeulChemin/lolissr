// ==================================================
// Prefetch Series
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

/*
|------------------------------------------------------------------
| Helpers
|------------------------------------------------------------------
*/

export function buildAjaxUrl(
    href,
)
{
    const url =
        new URL(
            href,
            window.location.origin,
        );

    return url.pathname;
}

export function getPrefetchedPage(
    url,
)
{
    return (
        prefetchedPages.get(url)
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

/*
|------------------------------------------------------------------
| Prefetch
|------------------------------------------------------------------
*/

export async function prefetchSeriesPage(
    href,
)
{
    try {

        const ajaxUrl =
            buildAjaxUrl(
                href,
            );

        /*
        |----------------------------------------------------------
        | Already cached
        |----------------------------------------------------------
        */

        if (
            prefetchedPages.has(
                ajaxUrl,
            )
        ) {
            return;
        }

        /*
        |----------------------------------------------------------
        | Already fetching
        |----------------------------------------------------------
        */

        if (
            pendingRequests.has(
                ajaxUrl,
            )
        ) {
            return;
        }

        pendingRequests.add(
            ajaxUrl,
        );

        const response =
            await fetch(
                ajaxUrl,
                {
                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                },
            );

        if (!response.ok) {

            pendingRequests.delete(
                ajaxUrl,
            );

            return;
        }

        const html =
            await response.text();

        storePrefetchedPage(
            ajaxUrl,
            html,
        );

        pendingRequests.delete(
            ajaxUrl,
        );

        console.log(
            '[PREFETCH]',
            ajaxUrl,
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
| Next Page
|------------------------------------------------------------------
*/

function prefetchNextPaginationPage()
{
    const nextPagination =
        document.querySelector(
            '.collection-pagination-link.active + .collection-pagination-link',
        );

    if (!nextPagination) {
        return;
    }

    prefetchSeriesPage(
        nextPagination.href,
    );
}

/*
|------------------------------------------------------------------
| Hover
|------------------------------------------------------------------
*/

function bindHoverPrefetch()
{
    const links =
        document.querySelectorAll(
            '.collection-pagination-link',
        );

    links.forEach(
        link =>
        {
            link.addEventListener(
                'mouseenter',
                () =>
                {
                    prefetchSeriesPage(
                        link.href,
                    );
                },
                {
                    passive: true,
                },
            );
        },
    );
}

/*
|------------------------------------------------------------------
| Init
|------------------------------------------------------------------
*/

export function initPrefetchSeries()
{
    /*
    |--------------------------------------------------------------
    | First page
    |--------------------------------------------------------------
    */

    prefetchNextPaginationPage();

    /*
    |--------------------------------------------------------------
    | Hover links
    |--------------------------------------------------------------
    */

    bindHoverPrefetch();

    /*
    |--------------------------------------------------------------
    | After AJAX reload
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'ajax:series-loaded',
        () =>
        {
            prefetchNextPaginationPage();

            bindHoverPrefetch();
        },
    );
}