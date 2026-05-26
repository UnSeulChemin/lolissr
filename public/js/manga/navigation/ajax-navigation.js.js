// ==================================================
// AJAX Navigation
// ==================================================

import {
    fetchPageHtml,
} from './ajax-fetch.js';

import {
    replaceContent,
} from './ajax-dom.js';

import {
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

/*
|------------------------------------------------------------------
| Loading
|------------------------------------------------------------------
*/

function showLoadingState(
    container,
)
{
    container.classList.add(
        'is-loading',
    );
}

function hideLoadingState(
    container,
)
{
    container.classList.remove(
        'is-loading',
    );
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

    showLoadingState(
        container,
    );

    /*
    |--------------------------------------------------------------
    | Update URL immediately
    |--------------------------------------------------------------
    */

    if (updateHistory) {

        window.history.pushState(
            {},
            '',
            href,
        );
    }

    try {

        const html =
            await fetchPageHtml(
                href,
            );

        /*
        |--------------------------------------------------------------
        | Ignore old request
        |--------------------------------------------------------------
        */

        if (
            requestId
            !== currentRequestId
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------
        | Replace content
        |--------------------------------------------------------------
        */

        await replaceContent(
            html,
        );

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

            hideLoadingState(
                container,
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
    | Native Browser Behavior
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

    initialized =
        true;

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