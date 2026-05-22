// Cache mémoire pour les préfetchs
const cache = {
    links: new Set(),
    linksPending: new Set(),
    seriesPages: new Set(),
    seriesPagesPending: new Set(),
    seriesImages: new Set(),
    hoverTimers: new WeakMap(),
};

// Vérifie si un lien peut être préfetché
function canPrefetchLink(link) {
    if (!link?.href) return false;
    if (link.target === '_blank' || link.hasAttribute('download') || link.rel === 'nofollow') return false;
    if (link.href.startsWith('#') || link.href.startsWith('mailto:') || link.href.startsWith('tel:') || link.href.startsWith('javascript:')) return false;
    if (link.classList.contains('collection-card-link') || link.classList.contains('collection-pagination-link')) return false;
    const url = new URL(link.href, window.location.origin);
    if (url.origin !== window.location.origin) return false;
    if (url.pathname.includes('/ajax/') || url.pathname.endsWith('/manga')) return false;
    const absoluteUrl = url.toString();
    return !cache.links.has(absoluteUrl) && !cache.linksPending.has(absoluteUrl);
}

// Prefetch général d'un lien
async function prefetchLink(url) {
    if (!url || cache.links.has(url) || cache.linksPending.has(url)) return;
    cache.linksPending.add(url);
    try {
        const res = await fetch(url, { method: 'GET', credentials: 'same-origin' });
        if (res.ok) cache.links.add(url);
    } catch {}
    finally { cache.linksPending.delete(url); }
}

function scheduleLinkPrefetch(link) {
    const existingTimer = cache.hoverTimers.get(link);
    if (existingTimer) clearTimeout(existingTimer);
    const timer = setTimeout(() => { prefetchLink(link.href); cache.hoverTimers.delete(link); }, 120);
    cache.hoverTimers.set(link, timer);
}

function cancelLinkPrefetch(link) {
    const timer = cache.hoverTimers.get(link);
    if (!timer) return;
    clearTimeout(timer);
    cache.hoverTimers.delete(link);
}

// Initialisation globale pour tous les liens
export function initPrefetchLinks() {
    if (navigator.connection?.saveData) return; // économiser données
    if (document.body.dataset.prefetchLinksInit === 'true') return;
    document.body.dataset.prefetchLinksInit = 'true';

    document.addEventListener('pointerover', e => {
        if (e.pointerType && e.pointerType !== 'mouse') return;
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
        prefetchLink(link.href);
    });
}

// Prefetch spécifique pour les pages et images de séries
function canPrefetchSeriesUrl(url) { return url && !cache.seriesPages.has(url) && !cache.seriesPagesPending.has(url); }
function canPrefetchSeriesImage(url) { return url && !cache.seriesImages.has(url); }

export function prefetchSeriesPage(url) {
    if (!canPrefetchSeriesUrl(url)) return;
    cache.seriesPagesPending.add(url);
    fetch(url, { method: 'GET', credentials: 'same-origin' })
        .then(res => { if (res.ok) cache.seriesPages.add(url); })
        .catch(() => {})
        .finally(() => { cache.seriesPagesPending.delete(url); });
}

export function prefetchSeriesImage(url) {
    if (!canPrefetchSeriesImage(url)) return;
    cache.seriesImages.add(url);
    const img = new Image();
    img.src = url;
}

function getSeriesCardLinkFromEventTarget(target) { return target?.closest('.collection-card-link') ?? null; }

function scheduleSeriesCardPrefetch(cardLink) {
    const existingTimer = cache.hoverTimers.get(cardLink);
    if (existingTimer) clearTimeout(existingTimer);
    const timer = setTimeout(() => {
        prefetchSeriesPage(cardLink.href);
        const img = cardLink.querySelector('.card-image-portrait');
        if (img) prefetchSeriesImage(img.src);
        cache.hoverTimers.delete(cardLink);
    }, 120);
    cache.hoverTimers.set(cardLink, timer);
}

function cancelSeriesCardPrefetchHover(cardLink) {
    const timer = cache.hoverTimers.get(cardLink);
    if (!timer) return;
    clearTimeout(timer);
    cache.hoverTimers.delete(cardLink);
}

export function initPrefetchSeries() {
    if (document.body.dataset.prefetchSeriesInit === 'true') return;
    document.body.dataset.prefetchSeriesInit = 'true';

    document.addEventListener('pointerover', e => {
        if (e.pointerType && e.pointerType !== 'mouse') return;
        const cardLink = getSeriesCardLinkFromEventTarget(e.target);
        if (!cardLink) return;
        if (getSeriesCardLinkFromEventTarget(e.relatedTarget) === cardLink) return;
        scheduleSeriesCardPrefetch(cardLink);
    });

    document.addEventListener('pointerout', e => {
        const cardLink = getSeriesCardLinkFromEventTarget(e.target);
        if (!cardLink) return;
        if (getSeriesCardLinkFromEventTarget(e.relatedTarget) === cardLink) return;
        cancelSeriesCardPrefetchHover(cardLink);
    });

    document.addEventListener('focusin', e => {
        const cardLink = getSeriesCardLinkFromEventTarget(e.target);
        if (!cardLink) return;
        prefetchSeriesPage(cardLink.href);
        const img = cardLink.querySelector('.card-image-portrait');
        if (img) prefetchSeriesImage(img.src);
    });
}