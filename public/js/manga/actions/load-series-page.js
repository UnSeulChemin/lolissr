// ==================================================
// Load Series Page
// ==================================================

import {
    buildAjaxUrl,
    getPrefetchedPage,
    prefetchSeriesPage
} from '../navigation/prefetch-series.js';

const containerSelector = '.collection-ajax-container';
const contentSelector = '.collection-ajax-content';

/* ----------------- Helpers ----------------- */

function getContainer() {
    return document.querySelector(containerSelector);
}

function getContent() {
    return document.querySelector(contentSelector);
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

function isSeriesPageUrl(url) {
    const pathname =
        typeof url === 'string'
            ? new URL(url).pathname
            : url.pathname;

    return /\/manga\/series($|\/page\/\d+$)/.test(pathname);
}

/* ----------------- Fetch HTML ----------------- */

async function fetchHtml(ajaxUrl) {
    const cached = getPrefetchedPage(ajaxUrl);

    if (cached) {
        return cached;
    }

    const response = await fetch(
        `${ajaxUrl}?t=${Date.now()}`,
        {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }
    );

    if (!response.ok) {
        throw new Error('Erreur AJAX');
    }

    return await response.text();
}

/* ----------------- Load Page ----------------- */

export async function loadSeriesPage(
    href,
    pushState = true
) {
    const container = getContainer();
    const content = getContent();

    if (!container || !content) {
        return;
    }

    container.classList.add('is-loading');

    try {
        const ajaxUrl = buildAjaxUrl(href);

        const html = await fetchHtml(ajaxUrl);

        const parser = new DOMParser();

        const doc = parser.parseFromString(
            html,
            'text/html'
        );

        const newContent =
            doc.querySelector(contentSelector);

        if (!newContent) {
            throw new Error(
                '[AJAX] New content not found'
            );
        }

        // Replace content
        content.innerHTML = newContent.innerHTML;

        // Update URL
        if (pushState) {
            window.history.pushState({}, '', href);
        }

        // Events
        document.dispatchEvent(
            new CustomEvent('ajax:series-loaded')
        );

        scrollToTop();

        // Prefetch next page
        const active =
            document.querySelector(
                '.collection-pagination-link.active'
            ) ||
            document.querySelector(
                '.collection-pagination-link'
            );

        const next = active?.nextElementSibling;

        if (
            next?.classList.contains(
                'collection-pagination-link'
            )
        ) {
            prefetchSeriesPage(next.href);
        }

    } catch (error) {
        console.error(
            '[AJAX] loadSeriesPage failed',
            error
        );
    } finally {
        container.classList.remove('is-loading');
    }
}

/* ----------------- Click Handler ----------------- */

async function handleClick(event) {
    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    // AJAX uniquement pour pagination
    const link = target.closest(
        'a.collection-pagination-link'
    );

    if (!link) {
        return;
    }

    // Navigation normale si:
    // ctrl/cmd/shift/middle click
    if (
        link.target === '_blank' ||
        event.ctrlKey ||
        event.metaKey ||
        event.shiftKey ||
        event.button === 1
    ) {
        return;
    }

    event.preventDefault();

    await loadSeriesPage(link.href);
}

/* ----------------- Popstate ----------------- */

async function handlePopState() {
    const href = window.location.href;

    if (!isSeriesPageUrl(href)) {
        return;
    }

    await loadSeriesPage(href, false);
}

/* ----------------- Init ----------------- */

export function initLoadSeriesPage() {

    if (
        document.body.dataset
            .loadSeriesPageInit === 'true'
    ) {
        return;
    }

    document.body.dataset
        .loadSeriesPageInit = 'true';

    const container = getContainer();

    if (!container) {
        return;
    }

    document.addEventListener(
        'click',
        handleClick
    );

    window.addEventListener(
        'popstate',
        handlePopState
    );

    // Initial prefetch
    const nextPage = document.querySelector(
        '.collection-pagination-link.active + .collection-pagination-link'
    );

    if (nextPage) {
        prefetchSeriesPage(nextPage.href);
    }
}

/* ----------------- DOM Ready ----------------- */

document.addEventListener(
    'DOMContentLoaded',
    initLoadSeriesPage
);