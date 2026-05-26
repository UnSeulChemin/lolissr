// =========================================
// AJAX NAVIGATION (SPA CORE CLEAN)
// =========================================

import { getPrefetchedPage } from './prefetch.js';
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
// ACTIVE NAV (HEADER UPDATE)
// =========================

function updateActiveNavigation()
{
    const current = new URL(location.href).pathname.replace(/\/+$/, '/');
    const links = document.querySelectorAll('.nav-link-icon');

    for (const link of links)
    {
        if (!(link instanceof HTMLAnchorElement)) continue;

        const href = new URL(link.href).pathname.replace(/\/+$/, '/');

        link.classList.remove('active');

        if (href === '/') {
            if (current === '/') link.classList.add('active');
            continue;
        }

        if (current === href || current.startsWith(href + '/')) {
            link.classList.add('active');
        }
    }
}

// =========================
// PREFETCH TRIGGER
// =========================

function prefetchVisible()
{
    const root = document.querySelector('.ajax-content');
    if (!root) return;

    const links = root.querySelectorAll(
        'a[href], a.nav-link-icon'
    );

    for (const link of links)
    {
        if (!(link instanceof HTMLAnchorElement)) continue;
        if (!link.href) continue;

        window.__prefetchPage?.(link.href);
    }
}

// =========================
// NAVIGATION CORE
// =========================

export async function navigateTo(href, options = {})
{
    if (lock) return;

    const currentId = ++id;

    const target = normalizeUrl(href);
    const current = normalizeUrl(location.href);

    if (target === current) return;

    controller?.abort();
    controller = new AbortController();

    lock = true;
    document.body.dataset.ajaxNavigating = '1';

    try {

        let html = getPrefetchedPage(target);

        if (!html) {
            html = await fetchPageHtml(target, {
                signal: controller.signal
            });
        }

        if (!html) throw new Error('Empty HTML');

        if (currentId !== id) return;

        history.pushState({}, '', target);

        await runPageTransition(() =>
        {
            if (currentId !== id) return;

            replaceContent(html);
            updateActiveNavigation();
        });

        if (options.scrollTop !== false) {
            scrollTop(false);
        }

        document.dispatchEvent(new CustomEvent('ajax:page-loaded'));

        requestAnimationFrame(prefetchVisible);

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

    updateActiveNavigation();

    setTimeout(prefetchVisible, 200);

    debug('AJAX', 'SPA ready');
}