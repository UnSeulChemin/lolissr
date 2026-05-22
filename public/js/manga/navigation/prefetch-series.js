// ==============================================
// Prefetch Series
// ==============================================

const prefetchedPages =
    new Map();

const prefetchedImages =
    new Set();

/*
|------------------------------------------------------------------
| Helpers
|------------------------------------------------------------------
*/

function getBasePath()
{
    return '/lolissr';
}

function normalizeUrl(
    url,
)
{
    return url;
}

function isElement(
    target,
)
{
    return (
        target
        instanceof Element
    );
}

/*
|------------------------------------------------------------------
| Build AJAX URL
|------------------------------------------------------------------
*/

export function buildAjaxUrl(
    link,
)
{
    const url =
        new URL(
            link.href,
            window.location.origin,
        );

    const match =
        url.pathname.match(
            /\/manga\/series\/page\/(\d+)$/,
        );

    const page =
        match
            ? Math.max(
                1,
                parseInt(
                    match[1],
                    10,
                ),
            )
            : 1;

    url.pathname =
        `${getBasePath()}/manga/ajax/series/page/${page}`;

    return url.toString();
}

/*
|------------------------------------------------------------------
| Prefetch HTML
|------------------------------------------------------------------
*/

export async function prefetchSeriesPage(
    url,
)
{
    const ajaxUrl =
        buildAjaxUrl({
            href: url,
        });

    if (
        prefetchedPages.has(
            ajaxUrl,
        )
    ) {
        return prefetchedPages.get(
            ajaxUrl,
        );
    }

    try {

        const response =
            await fetch(
                ajaxUrl,
                {
                    headers:
                    {
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                },
            );

        if (
            !response.ok
        ) {

            console.warn(
                'Prefetch failed:',
                ajaxUrl,
            );

            return null;
        }

        const html =
            await response.text();

        prefetchedPages.set(
            ajaxUrl,
            html,
        );

        return html;

    } catch (error) {

        console.error(
            'Prefetch failed:',
            error,
        );

        return null;
    }
}

/*
|------------------------------------------------------------------
| Prefetch images
|------------------------------------------------------------------
*/

export function prefetchSeriesImage(
    url,
)
{
    if (
        !url
        || prefetchedImages.has(
            url,
        )
    ) {
        return;
    }

    prefetchedImages.add(
        url,
    );

    const image =
        new Image();

    image.src =
        normalizeUrl(
            url,
        );
}

/*
|------------------------------------------------------------------
| Cache
|------------------------------------------------------------------
*/

export function getPrefetchedPage(
    url,
)
{
    return prefetchedPages.get(
        url,
    );
}

/*
|------------------------------------------------------------------
| Bind cards
|------------------------------------------------------------------
*/

function bindCards()
{
    const cards =
        document.querySelectorAll(
            '.collection-card-link',
        );

    cards.forEach(
        card =>
        {
            if (
                card.dataset
                    .prefetchBound
                === 'true'
            ) {
                return;
            }

            card.dataset
                .prefetchBound =
                    'true';

            const prefetch =
                () =>
                {
                    prefetchSeriesPage(
                        card.href,
                    );

                    const image =
                        card.querySelector(
                            '.card-image-portrait',
                        );

                    if (image) {

                        prefetchSeriesImage(
                            image.src,
                        );
                    }
                };

            card.addEventListener(
                'pointerenter',
                prefetch,
            );

            card.addEventListener(
                'focus',
                prefetch,
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
    if (
        document.body.dataset
            .prefetchSeriesInit
        === 'true'
    ) {
        return;
    }

    document.body.dataset
        .prefetchSeriesInit =
            'true';

    bindCards();

    document.addEventListener(
        'ajax:series-loaded',
        () =>
        {
            bindCards();
        },
    );

    /*
    |--------------------------------------------------------------
    | Global hover prefetch
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'pointerenter',
        event =>
        {
            if (
                !isElement(
                    event.target,
                )
            ) {
                return;
            }

            const card =
                event.target.closest(
                    '.collection-card-link',
                );

            if (!card) {
                return;
            }

            prefetchSeriesPage(
                card.href,
            );

            const image =
                card.querySelector(
                    '.card-image-portrait',
                );

            if (image) {

                prefetchSeriesImage(
                    image.src,
                );
            }

        },
        true,
    );
}