function normalizeUrl(url) {
    // Supprime '/public' ou '/lolissr' au début
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
    if (!url) return;
    try {
        const res = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (res.ok) {
            const html = await res.text();
            console.log('Prefetched:', url);
        } else {
            console.warn('404 prefetch:', url);
        }
    } catch (err) {
        console.error('Prefetch failed:', err);
    }
}

// Pour les cards
function setupCardPrefetch(card) {
    card.addEventListener('pointerenter', () => prefetchLink(card.href));
    card.addEventListener('focus', () => prefetchLink(card.href));
}

// Pagination
function setupPaginationPrefetch() {
    const active = document.querySelector('.collection-pagination-link.active');
    if (!active) return;
    const next = active.nextElementSibling;
    if (next?.classList.contains('collection-pagination-link')) {
        prefetchLink(next.href);
    }
}

// Initialisation
export function initPrefetchSeries() {
    const cards = document.querySelectorAll('.collection-card-link');
    cards.forEach(setupCardPrefetch);
    setupPaginationPrefetch();
}