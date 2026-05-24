// ==================================================
// Load Series Page
// ==================================================

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
| State
|------------------------------------------------------------------
*/

let initialized = false;

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

function scrollToTop()
{
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
}

function isSeriesPageUrl(url)
{
    const pathname =
        typeof url === 'string'
            ? new URL(
                url,
                window.location.origin,
            ).pathname
            : url.pathname;

    return /\/manga\/series($|\/page\/\d+$)/.test(
        pathname,
    );
}

/*
|------------------------------------------------------------------
| Fetch
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
                headers: {
                    'X-Requested-With':
                        'XMLHttpRequest',
                },
            },
        );

    if (!response.ok) {

        throw new Error(
            '[AJAX] Failed request',
        );
    }

    return await response.text();
}

/*
|------------------------------------------------------------------
| Replace content
|------------------------------------------------------------------
*/

function replaceContent(
    html,
)
{
    const parser =
        new DOMParser();

    const doc =
        parser.parseFromString(
            html,
            'text/html',
        );

    const newContent =
        doc.querySelector(
            contentSelector,
        );

    if (!newContent) {

        throw new Error(
            '[AJAX] Missing new content',
        );
    }

    const content =
        getContent();

    if (!content) {
        return;
    }

    content.innerHTML =
        newContent.innerHTML;
}

/*
|------------------------------------------------------------------
| Prefetch
|------------------------------------------------------------------
*/

function prefetchNextPage()
{
    const nextPage =
        document.querySelector(
            '.collection-pagination-link.active + .collection-pagination-link',
        );

    if (!nextPage) {
        return;
    }

    prefetchSeriesPage(
        nextPage.href,
    );
}

/*
|------------------------------------------------------------------
| Load
|------------------------------------------------------------------
*/

export async function loadSeriesPage(
    href,
    pushState = true,
)
{
    const container =
        getContainer();

    if (!container) {
        return;
    }

    container.classList.add(
        'is-loading',
    );

    try {

        const ajaxUrl =
            buildAjaxUrl(
                href,
            );

        const html =
            await fetchHtml(
                ajaxUrl,
            );

        replaceContent(
            html,
        );

        if (pushState) {

            window.history.pushState(
                {},
                '',
                href,
            );
        }

        document.dispatchEvent(
            new CustomEvent(
                'ajax:series-loaded',
            ),
        );

        scrollToTop();

        prefetchNextPage();

    } catch (error) {

        console.error(
            '[AJAX]',
            error,
        );

    } finally {

        container.classList.remove(
            'is-loading',
        );
    }
}

/*
|------------------------------------------------------------------
| Click
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
            '.collection-pagination-link',
        );

    if (
        !link
        || !(link instanceof HTMLAnchorElement)
    ) {
        return;
    }

    if (
        !isSeriesPageUrl(
            link.href,
        )
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Native browser behavior
    |--------------------------------------------------------------
    */

    if (
        link.target === '_blank'
        || event.ctrlKey
        || event.metaKey
        || event.shiftKey
        || event.button === 1
    ) {
        return;
    }

    event.preventDefault();

    await loadSeriesPage(
        link.href,
    );
}

/*
|------------------------------------------------------------------
| Browser Navigation
|------------------------------------------------------------------
*/

async function handlePopState()
{
    const href =
        window.location.href;

    if (
        !isSeriesPageUrl(
            href,
        )
    ) {
        return;
    }

    await loadSeriesPage(
        href,
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
    if (initialized) {
        return;
    }

    initialized = true;

    const container =
        getContainer();

    if (!container) {
        return;
    }

    document.addEventListener(
        'click',
        handleClick,
    );

    window.addEventListener(
        'popstate',
        handlePopState,
    );

    prefetchNextPage();
}