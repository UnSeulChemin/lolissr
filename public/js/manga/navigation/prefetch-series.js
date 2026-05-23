// ==================================================
// Prefetch Series
// ==================================================

const prefetchedPages = new Map();
const prefetchedImages = new Set();

/* ----------------- Helpers ----------------- */
function getBasePath() {
    return '/lolissr';
}

function isElement(target) {
    return target instanceof Element;
}

/* ----------------- Build AJAX URL ----------------- */
export function buildAjaxUrl(link) {
    const href = link.href ?? link;
    const url = new URL(href, window.location.origin);

    const match = url.pathname.match(/\/manga\/series\/page\/(\d+)$/);
    const page = match ? Math.max(1, parseInt(match[1], 10)) : 1;

    url.pathname = `${getBasePath()}/manga/ajax/series/page/${page}`;
    return url.toString();
}

/* ----------------- Prefetch HTML ----------------- */
export async function prefetchSeriesPage(url) {
    const ajaxUrl = buildAjaxUrl(url);
    if (prefetchedPages.has(ajaxUrl)) return prefetchedPages.get(ajaxUrl);

    try {
        const res = await fetch(ajaxUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) return null;

        const html = await res.text();
        prefetchedPages.set(ajaxUrl, html);
        return html;
    } catch (err) {
        console.error('Prefetch failed:', err);
        return null;
    }
}

/* ----------------- Prefetch Images ----------------- */
export function prefetchSeriesImage(url) {
    if (!url || prefetchedImages.has(url)) return;
    prefetchedImages.add(url);

    const img = new Image();
    img.src = url;
}

/* ----------------- Cache access ----------------- */
export function getPrefetchedPage(url) {
    return prefetchedPages.get(url);
}

/* ----------------- Bind cards ----------------- */
function bindCards() {
    const cards = document.querySelectorAll('.collection-card-link');
    cards.forEach(card => {
        if (card.dataset.prefetchBound === 'true') return;
        card.dataset.prefetchBound = 'true';

        const prefetch = () => {
            prefetchSeriesPage(card.href);
            const img = card.querySelector('.card-image-portrait');
            if (img) prefetchSeriesImage(img.src);
        };

        card.addEventListener('pointerenter', prefetch);
        card.addEventListener('focus', prefetch);
    });
}

/* ----------------- Init ----------------- */
export function initPrefetchSeries() {
    if (document.body.dataset.prefetchSeriesInit === 'true') return;
    document.body.dataset.prefetchSeriesInit = 'true';

    bindCards();
    document.addEventListener('ajax:series-loaded', bindCards);

    // Préfetch global sur hover de n'importe quelle card
    document.addEventListener('pointerenter', event => {
        if (!isElement(event.target)) return;

        const card = event.target.closest('.collection-card-link');
        if (!card) return;

        prefetchSeriesPage(card.href);
        const img = card.querySelector('.card-image-portrait');
        if (img) prefetchSeriesImage(img.src);
    }, true);
}