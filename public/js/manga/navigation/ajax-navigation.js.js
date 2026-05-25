// ==================================================
// AJAX Navigation
// ==================================================

import {
    getPrefetchedPage,
    prefetchPage,
} from './prefetch-series.js';

/*
|------------------------------------------------------------------
| State
|------------------------------------------------------------------
*/

let initialized =
    false;

let currentRequestId =
    0;

/*
|------------------------------------------------------------------
| Selectors
|------------------------------------------------------------------
*/

const containerSelector =
    '.collection-ajax-container';

const contentSelector =
    '.collection-ajax-content';

const paginationSelector =
    '.collection-pagination-link';

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

function isPaginationLink(
    link,
)
{
    return (
        link instanceof HTMLAnchorElement
        && link.matches(
            paginationSelector,
        )
    );
}

function delay(
    duration,
)
{
    return new Promise(
        (resolve) =>
        {
            window.setTimeout(
                resolve,
                duration,
            );
        },
    );
}

/*
|------------------------------------------------------------------
| Scroll
|------------------------------------------------------------------
*/

function scrollToTop()
{
    window.scrollTo({
        top: 0,
        behavior: 'instant',
    });
}

/*
|------------------------------------------------------------------
| Animations
|------------------------------------------------------------------
*/

async function animateContentOut(
    content,
)
{
    content.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );

    content.classList.add(
        'page-transition-out',
    );

    await delay(
        160,
    );
}

async function animateContentIn(
    content,
)
{
    content.classList.remove(
        'page-transition-out',
    );

    content.classList.add(
        'page-transition-in',
    );

    requestAnimationFrame(
        () =>
        {
            content.classList.add(
                'page-transition-visible',
            );
        },
    );

    await delay(
        240,
    );

    content.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );
}

/*
|------------------------------------------------------------------
| HTML
|------------------------------------------------------------------
*/

function extractNewContent(
    html,
)
{
    const parser =
        new DOMParser();

    const documentHtml =
        parser.parseFromString(
            html,
            'text/html',
        );

    return documentHtml.querySelector(
        contentSelector,
    );
}

function updateDocumentTitle(
    html,
)
{
    const parser =
        new DOMParser();

    const documentHtml =
        parser.parseFromString(
            html,
            'text/html',
        );

    const title =
        documentHtml.querySelector(
            'title',
        );

    if (
        title
        && title.textContent
    ) {
        document.title =
            title.textContent;
    }
}

/*
|------------------------------------------------------------------
| Replace Content
|------------------------------------------------------------------
*/

async function replaceContent(
    html,
)
{
    const currentContent =
        getContent();

    if (!currentContent) {
        return;
    }

    const newContent =
        extractNewContent(
            html,
        );

    if (!newContent) {

        throw new Error(
            '[AJAX] Missing content',
        );
    }

    await animateContentOut(
        currentContent,
    );

    currentContent.innerHTML =
        newContent.innerHTML;

    scrollToTop();

    await animateContentIn(
        currentContent,
    );
}

/*
|------------------------------------------------------------------
| Fetch
|------------------------------------------------------------------
*/

async function fetchPageHtml(
    href,
)
{
    const cached =
        getPrefetchedPage(
            href,
        );

    if (cached) {
        return cached;
    }

    const response =
        await fetch(
            href,
            {
                headers: {
                    'X-Requested-With':
                        'XMLHttpRequest',
                },
            },
        );

    if (!response.ok) {

        throw new Error(
            '[AJAX] Request failed',
        );
    }

    return await response.text();
}

/*
|------------------------------------------------------------------
| Load Page
|------------------------------------------------------------------
*/

export async function loadAjaxPage(
    href,
    updateHistory = true,
)
{
    const requestId =
        ++currentRequestId;

    const container =
        getContainer();

    if (!container) {
        return;
    }

    container.classList.add(
        'is-loading',
    );

    try {

        const html =
            await fetchPageHtml(
                href,
            );

        /*
        |--------------------------------------------------------------
        | Ignore old requests
        |--------------------------------------------------------------
        */

        if (
            requestId
            !== currentRequestId
        ) {
            return;
        }

        updateDocumentTitle(
            html,
        );

        await replaceContent(
            html,
        );

        /*
        |--------------------------------------------------------------
        | History
        |--------------------------------------------------------------
        */

        if (updateHistory) {

            window.history.pushState(
                {},
                '',
                href,
            );
        }

        /*
        |--------------------------------------------------------------
        | Events
        |--------------------------------------------------------------
        */

        document.dispatchEvent(
            new CustomEvent(
                'ajax:page-loaded',
            ),
        );

        document.dispatchEvent(
            new CustomEvent(
                'ajax:series-loaded',
            ),
        );

        /*
        |--------------------------------------------------------------
        | Prefetch
        |--------------------------------------------------------------
        */

        requestAnimationFrame(
            () =>
            {
                prefetchVisibleLinks();
            },
        );

    } catch (error) {

        console.error(
            '[AJAX]',
            error,
        );

        window.location.href =
            href;

    } finally {

        if (
            requestId
            === currentRequestId
        ) {
            container.classList.remove(
                'is-loading',
            );
        }
    }
}

/*
|------------------------------------------------------------------
| Prefetch
|------------------------------------------------------------------
*/

function prefetchVisibleLinks()
{
    const links =
        document.querySelectorAll(
            paginationSelector,
        );

    for (const link of links) {

        if (
            link instanceof HTMLAnchorElement
        ) {
            prefetchPage(
                link.href,
            );
        }
    }
}

/*
|------------------------------------------------------------------
| Click Navigation
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
            paginationSelector,
        );

    if (
        !isPaginationLink(
            link,
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
        event.ctrlKey
        || event.metaKey
        || event.shiftKey
        || event.altKey
        || event.button === 1
        || link.target === '_blank'
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------
    | Same URL
    |--------------------------------------------------------------
    */

    if (
        link.href
        === window.location.href
    ) {
        return;
    }

    event.preventDefault();

    await loadAjaxPage(
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
    await loadAjaxPage(
        window.location.href,
        false,
    );
}

/*
|------------------------------------------------------------------
| Init
|------------------------------------------------------------------
*/

export function initAjaxNavigation()
{
    if (initialized) {
        return;
    }

    initialized = true;

    if (!getContainer()) {
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

    requestAnimationFrame(
        () =>
        {
            prefetchVisibleLinks();
        },
    );
}