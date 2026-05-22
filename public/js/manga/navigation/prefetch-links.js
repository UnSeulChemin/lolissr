import { buildAjaxUrl } from '../actions/load-series-page.js';

// --------------------------------------------------
// Cache mémoire & HTML
// --------------------------------------------------
const cache = {
    links: new Set(),
    linksPending: new Set(),
    seriesImages: new Set(),
    hoverTimers: new WeakMap(),
};

export const prefetchedPages = new Map();

// --------------------------------------------------
// Vérifie si un lien peut être préfetché
// --------------------------------------------------
function canPrefetchLink(link) {
    if (!link?.href) return false;
    if (link.target === '_blank' || link.hasAttribute('download') || link.rel === 'nofollow') return false;
    if (link.href.startsWith('#') || link.href.startsWith('mailto:') || link.href.startsWith('tel:') || link.href.startsWith('javascript:')) return false;

    // Exclure uniquement les liens cards série
    if (link.classList.contains('collection-card-link')) return false;

    const url = new URL(link.href, window.location.origin);
    if (url.origin !== window.location.origin) return false;

    // On supprime le bloc qui excluait /manga pour le header
    // if (url.pathname.includes('/ajax/') || url.pathname.endsWith('/manga')) return false;

    const absoluteUrl = url.toString();
    return !cache.links.has(absoluteUrl) && !cache.linksPending.has(absoluteUrl);
}

// --------------------------------------------------
// Prefetch HTML
// --------------------------------------------------
async function prefetchLink(url) {
    if (!url || cache.links.has(url) || cache.linksPending.has(url)) return;
    cache.linksPending.add(url);
    try {
        const res = await fetch(url, { method: 'GET', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (res.ok) {
            const html = await res.text();
            prefetchedPages.set(url, html);
            cache.links.add(url);
        }
    } catch {}
    finally { cache.linksPending.delete(url); }
}

// --------------------------------------------------
// Hover liens classiques
// --------------------------------------------------
function scheduleLinkPrefetch(link) {
    const existing = cache.hoverTimers.get(link);
    if (existing) clearTimeout(existing);
    const timer = setTimeout(() => {
        if (link.classList.contains('collection-pagination-link')) {
            prefetchLink(buildAjaxUrl(link));
        } else {
            prefetchLink(link.href);
        }
        cache.hoverTimers.delete(link);
    }, 120);
    cache.hoverTimers.set(link, timer);
}

function cancelLinkPrefetch(link) {
    const timer = cache.hoverTimers.get(link);
    if (!timer) return;
    clearTimeout(timer);
    cache.hoverTimers.delete(link);
}

// --------------------------------------------------
// Précharge page suivante pagination
// --------------------------------------------------
export function prefetchNextPaginationPage() {
    const active = document.querySelector('.collection-pagination-link.active');
    if (!active) return;
    const next = active.nextElementSibling;
    if (!next?.classList.contains('collection-pagination-link')) return;
    prefetchLink(buildAjaxUrl(next));
}

// --------------------------------------------------
// Préfetch image manga
// --------------------------------------------------
export function prefetchSeriesImage(url) {
    if (!url || cache.seriesImages.has(url)) return;
    cache.seriesImages.add(url);
    const img = new Image();
    img.src = url;
}

// --------------------------------------------------
// Cards mangas
// --------------------------------------------------
function getSeriesCardLink(target) {
    return target?.closest('.collection-card-link') ?? null;
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

// --------------------------------------------------
// Init préfetch liens globaux
// --------------------------------------------------
export function initPrefetchLinks() {
    if (navigator.connection?.saveData) return;
    if (document.body.dataset.prefetchLinksInit === 'true') return;
    document.body.dataset.prefetchLinksInit = 'true';

    document.addEventListener('pointerover', e => {
        const link = e.target.closest('a');
        if (!canPrefetchLink(link)) return;
        if (e.relatedTarget?.closest?.('a') === link) return;
        scheduleLinkPrefetch(link);
    });

    document.addEventListener('pointerout', e => {
        const link = e.target.closest('a');
        if (!link) return;
        if (e.relatedTarget?.closest?.('a') === link) return;
        cancelLinkPrefetch(link);
    });

    document.addEventListener('focusin', e => {
        const link = e.target.closest('a');
        if (!canPrefetchLink(link)) return;
        if (link.classList.contains('collection-pagination-link')) {
            prefetchLink(buildAjaxUrl(link));
        } else {
            prefetchLink(link.href);
        }
    });

    prefetchNextPaginationPage();
}

// --------------------------------------------------
// Init préfetch mangas
// --------------------------------------------------
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