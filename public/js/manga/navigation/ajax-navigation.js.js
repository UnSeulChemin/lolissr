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

async function animateContentOut(
    content,
)
{
    content.classList.add(
        'page-transition-out',
    );

    await new Promise(
        (resolve) =>
        {
            window.setTimeout(
                resolve,
                180,
            );
        },
    );
}

async function animateContentIn(
    content,
)
{
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

    await new Promise(
        (resolve) =>
        {
            window.setTimeout(
                resolve,
                220,
            );
        },
    );

    content.classList.remove(
        'page-transition-in',
        'page-transition-visible',
    );
}

async function replaceContent(
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

    const newContent =
        documentHtml.querySelector(
            contentSelector,
        );

    if (!newContent) {

        throw new Error(
            '[AJAX] Missing content',
        );
    }

    const currentContent =
        getContent();

    if (!currentContent) {
        return;
    }

    await animateContentOut(
        currentContent,
    );

    currentContent.innerHTML =
        newContent.innerHTML;

    await animateContentIn(
        currentContent,
    );
}

function scrollToTop()
{
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
}

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

        await replaceContent(
            html,
        );

        /*
        |--------------------------------------------------------------
        | History
        |--------------------------------------------------------------
        */

        if (updateHistory) {

            window.history.replaceState(
                {},
                '',
                href,
            );
        }

        scrollToTop();

        document.dispatchEvent(
            new CustomEvent(
                'ajax:series-loaded',
            ),
        );

        /*
        |--------------------------------------------------------------
        | Prefetch next
        |--------------------------------------------------------------
        */

        prefetchPage(
            href,
        );

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
        || event.button === 1
        || link.target === '_blank'
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
}