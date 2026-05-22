import {
    buildAjaxUrl,
    getPrefetchedPage,
    prefetchSeriesPage,
} from '../navigation/prefetch-series.js';

/*
|------------------------------------------------------------------
| Selectors
|------------------------------------------------------------------
*/

const containerSelector =
    '.collection-ajax-container';

const contentSelector =
    '.collection-ajax-content';

/*
|------------------------------------------------------------------
| Helpers
|------------------------------------------------------------------
*/

function getContainer()
{
    return document.querySelector(
        containerSelector,
    );
}

function getContent()
{
    return document.querySelector(
        contentSelector,
    );
}

function isSeriesPageUrl(
    url,
)
{
    return /\/manga\/series($|\/page\/\d+$)/
        .test(
            url.pathname,
        );
}

function scrollToTop()
{
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
}

/*
|------------------------------------------------------------------
| Fetch HTML
|------------------------------------------------------------------
*/

async function fetchHtml(
    ajaxUrl,
)
{
    const cached =
        getPrefetchedPage(
            ajaxUrl,
        );

    if (cached) {
        return cached;
    }

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
        throw new Error(
            'Erreur AJAX',
        );
    }

    return await response.text();
}

/*
|------------------------------------------------------------------
| Replace content
|------------------------------------------------------------------
*/

async function loadSeriesPage(
    url,
    pushState = true,
)
{
    const container =
        getContainer();

    const currentContent =
        getContent();

    if (
        !container
        || !currentContent
    ) {
        return;
    }

    container.classList.add(
        'is-loading',
    );

    try {

        const ajaxUrl =
            buildAjaxUrl({
                href: url.href,
            });

        const html =
            await fetchHtml(
                ajaxUrl,
            );

        const parser =
            new DOMParser();

        const documentHtml =
            parser.parseFromString(
                html,
                'text/html',
            );

        const newContent =
            documentHtml.querySelector(
                contentSelector,
            );

        if (!newContent) {
            throw new Error(
                'Contenu AJAX introuvable',
            );
        }

        /*
        |----------------------------------------------------------
        | Replace node
        |----------------------------------------------------------
        */

        currentContent.replaceWith(
            newContent,
        );

        /*
        |----------------------------------------------------------
        | History
        |----------------------------------------------------------
        */

        if (pushState) {

            window.history.pushState(
                {},
                '',
                url.href,
            );
        }

        /*
        |----------------------------------------------------------
        | Events
        |----------------------------------------------------------
        */

        document.dispatchEvent(
            new CustomEvent(
                'ajax:series-loaded',
            ),
        );

        scrollToTop();

        prefetchNextPage();

    } catch (error) {

        console.error(
            error,
        );

        window.location.href =
            url.href;

    } finally {

        container.classList.remove(
            'is-loading',
        );
    }
}

/*
|------------------------------------------------------------------
| Prefetch next page
|------------------------------------------------------------------
*/

function prefetchNextPage()
{
    const active =
        document.querySelector(
            '.collection-pagination-link.active',
        );

    if (!active) {
        return;
    }

    const next =
        active.nextElementSibling;

    if (
        !next
        || !next.classList.contains(
            'collection-pagination-link',
        )
    ) {
        return;
    }

    prefetchSeriesPage(
        next.href,
    );
}

/*
|------------------------------------------------------------------
| Click navigation
|------------------------------------------------------------------
*/

async function handleClick(
    event,
)
{
    const target =
        event.target;

    if (
        !(target instanceof Element)
    ) {
        return;
    }

    const link =
        target.closest(
            'a',
        );

    if (!link) {
        return;
    }

    const url =
        new URL(
            link.href,
        );

    if (
        !isSeriesPageUrl(
            url,
        )
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Ignore detail pages
    |--------------------------------------------------------------
    */

    if (
        /\/manga\/series\/[^/]+\/\d+$/
            .test(
                url.pathname,
            )
    ) {
        return;
    }

    event.preventDefault();

    await loadSeriesPage(
        url,
    );
}

/*
|------------------------------------------------------------------
| Browser navigation
|------------------------------------------------------------------
*/

async function handlePopState()
{
    const url =
        new URL(
            window.location.href,
        );

    if (
        !isSeriesPageUrl(
            url,
        )
    ) {
        return;
    }

    await loadSeriesPage(
        url,
        false,
    );
}

/*
|------------------------------------------------------------------
| Init
|------------------------------------------------------------------
*/

export function initLoadSeriesPage()
{
    if (
        document.body.dataset
            .loadSeriesPageInit
        === 'true'
    ) {
        return;
    }

    document.body.dataset
        .loadSeriesPageInit =
            'true';

    const container =
        getContainer();

    if (!container) {
        return;
    }

    prefetchNextPage();

    document.addEventListener(
        'click',
        handleClick,
    );

    window.addEventListener(
        'popstate',
        handlePopState,
    );
}