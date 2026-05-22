/*
|------------------------------------------------------------------
| Prefetch pour pages et images des séries manga
|------------------------------------------------------------------
*/

const cache = {
    seriesPages: new Set(),
    seriesPagesPending: new Set(),
    seriesImages: new Set(),
    hoverTimers: new WeakMap(),
};

/**
 * Vérifie si une URL de page série peut être préfetchée.
 */
function canPrefetchSeriesUrl(url) {
    if (!url) return false;
    const normalizedUrl = new URL(url, window.location.origin).toString();
    return !cache.seriesPages.has(normalizedUrl) && !cache.seriesPagesPending.has(normalizedUrl);
}

/**
 * Vérifie si une URL d'image peut être préfetchée.
 */
function canPrefetchSeriesImage(url) {
    return Boolean(url) && !cache.seriesImages.has(url);
}

/**
 * Prefetch d'une page série.
 */
export function prefetchSeriesPage(url) {
    if (!canPrefetchSeriesUrl(url)) return;

    const normalizedUrl = new URL(url, window.location.origin).toString();
    cache.seriesPagesPending.add(normalizedUrl);

    fetch(normalizedUrl, { method: 'GET', credentials: 'same-origin' })
        .then((res) => {
            if (res.ok) cache.seriesPages.add(normalizedUrl);
        })
        .catch(() => {
            // silence volontaire si prefetch échoue
        })
        .finally(() => {
            cache.seriesPagesPending.delete(normalizedUrl);
        });
}

/**
 * Prefetch d'une image.
 */
export function prefetchSeriesImage(url) {
    if (!canPrefetchSeriesImage(url)) return;

    cache.seriesImages.add(url);

    const img = new Image();
    img.src = url;
}

/**
 * Récupère le lien de la card série depuis l'event target.
 */
function getSeriesCardLinkFromEventTarget(target) {
    return target?.closest('.collection-card-link') ?? null;
}

/**
 * Programme le prefetch d'une card série avec delay.
 */
function scheduleSeriesCardPrefetch(cardLink) {
    const existingTimer = cache.hoverTimers.get(cardLink);
    if (existingTimer) clearTimeout(existingTimer);

    const timer = setTimeout(() => {
        prefetchSeriesPage(cardLink.href);

        const image = cardLink.querySelector('.card-image-portrait');
        if (image) prefetchSeriesImage(image.src);

        cache.hoverTimers.delete(cardLink);
    }, 120);

    cache.hoverTimers.set(cardLink, timer);
}

/**
 * Annule le prefetch d'une card série si hover interrompu.
 */
function cancelSeriesCardPrefetchHover(cardLink) {
    const timer = cache.hoverTimers.get(cardLink);
    if (!timer) return;

    clearTimeout(timer);
    cache.hoverTimers.delete(cardLink);
}

/**
 * Initialise les listeners hover/focus pour le prefetch des séries.
 */
export function initPrefetchSeries() {
    if (document.body.dataset.prefetchSeriesInit === 'true') return;
    document.body.dataset.prefetchSeriesInit = 'true';

    // Hover souris
    document.addEventListener('pointerover', (event) => {
        if (event.pointerType && event.pointerType !== 'mouse') return;

        const cardLink = getSeriesCardLinkFromEventTarget(event.target);
        if (!cardLink) return;

        const previousCardLink = getSeriesCardLinkFromEventTarget(event.relatedTarget);
        if (previousCardLink === cardLink) return;

        scheduleSeriesCardPrefetch(cardLink);
    });

    document.addEventListener('pointerout', (event) => {
        const cardLink = getSeriesCardLinkFromEventTarget(event.target);
        if (!cardLink) return;

        const nextCardLink = getSeriesCardLinkFromEventTarget(event.relatedTarget);
        if (nextCardLink === cardLink) return;

        cancelSeriesCardPrefetchHover(cardLink);
    });

    // Focus clavier
    document.addEventListener('focusin', (event) => {
        const cardLink = getSeriesCardLinkFromEventTarget(event.target);
        if (!cardLink) return;

        prefetchSeriesPage(cardLink.href);

        const image = cardLink.querySelector('.card-image-portrait');
        if (image) prefetchSeriesImage(image.src);
    });
}