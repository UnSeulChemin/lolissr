// =========================================
// AJAX NAVIGATION (FINAL CLEAN SPA)
// =========================================

import { getPrefetchedPage, prefetchPage } from './prefetch.js';
import { normalizeUrl } from '../core/navigation.js';
import { fetchPageHtml } from './ajax-fetch.js';
import { replaceContent } from './ajax-dom.js';
import { runPageTransition, scrollTop } from '../core/page-transition.js';
import { debug, debugError } from '../core/debug.js';

// =========================
// STATE
// =========================

let id = 0;
let controller = null;
let lock = false;

// =========================
// ACTIVE NAVIGATION
// =========================

function updateActiveNavigation()
{
    const currentPath = new URL(location.href).pathname.replace(/\/+$/, '/');

    const links = document.querySelectorAll('.nav-link-icon');

    for (const link of links)
    {
        if (!(link instanceof HTMLAnchorElement)) continue;

        const hrefPath = new URL(link.href).pathname.replace(/\/+$/, '/');

        link.classList.remove('active');

        // =========================
        // HOME STRICT ONLY
        // =========================
        if (hrefPath === '/') {
            if (currentPath === '/') {
                link.classList.add('active');
            }
            continue;
        }

        // =========================
        // OTHER ROUTES ONLY
        // =========================
        if (
            currentPath === hrefPath ||
            currentPath.startsWith(hrefPath + '/')
        ) {
            link.classList.add('active');
        }
    }
}

// =========================
// PREFETCH VISIBLES
// =========================

function prefetchVisible()
{
    if (lock) return;

    const links = document.querySelectorAll(
        'a.card-link, a.dashboard-card, a.collection-pagination-link, a[data-prefetch="true"]'
    );

    for (const link of links)
    {
        if (!(link instanceof HTMLAnchorElement)) continue;
        if (!link.href) continue;

        prefetchPage(link.href);
    }
}

// =========================
// NAVIGATION CORE
// =========================

export async function navigateTo(href, options = {})
{
    const currentId = ++id;

    const target = normalizeUrl(href);
    const current = normalizeUrl(location.href);

    if (target === current) return;

    controller?.abort();
    controller = new AbortController();

    lock = true;
    document.body.dataset.ajaxNavigating = '1';

    try {

        // =========================
        // CACHE FIRST
        // =========================

        let html = getPrefetchedPage(target);

        if (!html) {
            html = await fetchPageHtml(target, {
                signal: controller.signal
            });
        }

        if (!html) throw new Error('Empty HTML');

        if (currentId !== id) return;

        // =========================
        // HISTORY
        // =========================

        if (options.updateHistory !== false) {
            history.pushState({}, '', target);
        }

        // =========================
        // TRANSITION + DOM SWAP
        // =========================

        await runPageTransition(() =>
        {
            if (currentId !== id) return;

            replaceContent(html);
            updateActiveNavigation();
        });

        if (currentId !== id) return;

        // =========================
        // SCROLL
        // =========================

        if (options.scrollTop !== false) {
            scrollTop(false);
        }

        // =========================
        // EVENT
        // =========================

        document.dispatchEvent(
            new CustomEvent('ajax:page-loaded')
        );

        // =========================
        // PREFETCH NEXT
        // =========================

        prefetchVisible();

        debug('AJAX', 'done', target);

    } catch (e) {

        if (e?.name !== 'AbortError') {
            debugError('AJAX', e);
        }

    } finally {

        if (currentId === id) {
            lock = false;
            delete document.body.dataset.ajaxNavigating;
        }
    }
}

// =========================
// INIT
// =========================

export function initAjaxNavigation()
{
    if (window.__SPA__) return;
    window.__SPA__ = true;

    document.addEventListener('click', (e) =>
    {
        const link = e.target.closest('a[href]');
        if (!link) return;

        if (link.dataset.noAjax !== undefined) return;

        e.preventDefault();
        navigateTo(link.href);
    });

    window.addEventListener('popstate', () =>
    {
        navigateTo(location.href, {
            updateHistory: false,
            scrollTop: false,
        });
    });

    // initial active state
    updateActiveNavigation();

    setTimeout(prefetchVisible, 300);

    debug('AJAX', 'SPA ready');
}