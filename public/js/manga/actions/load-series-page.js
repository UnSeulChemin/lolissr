import { prefetchedPages } from '../navigation/prefetch-links.js';

const seriesPageCache = new Map();
const cache = {
    links: new Set(),
    linksPending: new Set(),
    seriesImages: new Set(),
    hoverTimers: new WeakMap(),
};

const containerSelector = '.collection-ajax-container';
const contentSelector = '.collection-ajax-content';

const getContainer = () => document.querySelector(containerSelector);
const getContent = () => document.querySelector(contentSelector);
const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });
const isSeriesPage = () => /\/manga\/series($|\/page\/\d+$)/.test(window.location.pathname);

function normalizeUrl(url) {
    // Supprime '/public' ou '/lolissr' si présent au début
    return url.replace(/^\/(public|lolissr)/, '');
}

export function buildAjaxUrl(link) {
    const url = new URL(normalizeUrl(link.href), window.location.origin);
    const match = url.pathname.match(/\/manga\/series\/page\/(\d+)$/);
    const page = match ? Math.max(1, parseInt(match[1], 10)) : 1;
    url.pathname = `/manga/ajax/series/page/${page}`;
    return url.toString();
}

async function prefetchLink(url) {
    url = normalizeUrl(url);
    if (!url || cache.links.has(url) || cache.linksPending.has(url)) return;
    cache.linksPending.add(url);
    console.log('Prefetch URL:', url);
    try {
        const res = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (res.ok) {
            const html = await res.text();
            seriesPageCache.set(url, html);
            cache.links.add(url);
        }
    } catch (err) {
        console.error('Prefetch failed:', err);
    } finally {
        cache.linksPending.delete(url);
    }
}

function scheduleSeriesCardPrefetch(card) {
    const existing = cache.hoverTimers.get(card);
    if (existing) clearTimeout(existing);
    const timer = setTimeout(() => {
        prefetchLink(card.href);
        const img = card.querySelector('.card-image-portrait');
        if (img) prefetchSeriesImage(img.src);
        cache.hoverTimers.delete(card);
    }, 120);
    cache.hoverTimers.set(card, timer);
}

function cancelSeriesCardPrefetch(card) {
    const timer = cache.hoverTimers.get(card);
    if (!timer) return;
    clearTimeout(timer);
    cache.hoverTimers.delete(card);
}

export function prefetchSeriesImage(url) {
    if (!url || cache.seriesImages.has(url)) return;
    cache.seriesImages.add(url);
    const img = new Image();
    img.src = normalizeUrl(url);
}

async function fetchHtml(url) {
    url = normalizeUrl(url);
    if (seriesPageCache.has(url)) return seriesPageCache.get(url);
    if (prefetchedPages.has(url)) return prefetchedPages.get(url);
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) throw new Error('Erreur AJAX');
    const html = await res.text();
    seriesPageCache.set(url, html);
    return html;
}

async function loadSeries(url, fallback) {
    const container = getContainer();
    const content = getContent();
    if (!container || !content) return;
    container.classList.add('is-loading');
    try {
        const html = await fetchHtml(url);
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector(contentSelector);
        if (!newContent) throw new Error('Contenu AJAX introuvable');
        content.replaceWith(newContent);
        document.dispatchEvent(new CustomEvent('ajax:series-loaded'));
        scrollToTop();
        requestAnimationFrame(() => prefetchNextPaginationPage());
    } catch (err) {
        console.error(err);
        window.location.href = fallback;
    } finally {
        container.classList.remove('is-loading');
    }
}

export function initLoadSeriesPage() {
    const container = getContainer();
    if (!container) return;
    prefetchNextPaginationPage();

    document.addEventListener('click', async e => {
        const link = e.target.closest('.collection-pagination-link');
        if (!link || !container.contains(link)) return;
        e.preventDefault();
        const ajaxUrl = buildAjaxUrl(link);
        await loadSeries(ajaxUrl, link.href);
        history.pushState({ ajaxUrl }, '', link.href);
    });

    window.addEventListener('popstate', async () => {
        if (!isSeriesPage()) return;
        const fakeLink = { href: window.location.href };
        await loadSeries(buildAjaxUrl(fakeLink), window.location.href);
    });
}

export function prefetchNextPaginationPage() {
    const active = document.querySelector('.collection-pagination-link.active');
    if (!active) return;
    const next = active.nextElementSibling;
    if (!next?.classList.contains('collection-pagination-link')) return;
    prefetchLink(buildAjaxUrl(next));
}

export function initPrefetchSeries() {
    if (document.body.dataset.prefetchSeriesInit === 'true') return;
    document.body.dataset.prefetchSeriesInit = 'true';

    const bindCards = () => {
        const cards = document.querySelectorAll('.collection-card-link');
        cards.forEach(card => {
            if (card.dataset.prefetchBound === 'true') return;
            card.dataset.prefetchBound = 'true';
            card.addEventListener('pointerenter', () => scheduleSeriesCardPrefetch(card));
            card.addEventListener('pointerleave', () => cancelSeriesCardPrefetch(card));
            card.addEventListener('focus', () => {
                prefetchLink(card.href);
                const img = card.querySelector('.card-image-portrait');
                if (img) prefetchSeriesImage(img.src);
            });
        });
    };

    bindCards();
    document.addEventListener('ajax:series-loaded', bindCards);
}

// Expose pour debug
if (typeof window !== 'undefined') {
    window.initPrefetchSeries = initPrefetchSeries;
    window.prefetchSeriesImage = prefetchSeriesImage;
    window.prefetchNextPaginationPage = prefetchNextPaginationPage;
}