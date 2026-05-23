import { buildAjaxUrl, getPrefetchedPage, prefetchSeriesPage } from '../navigation/prefetch-series.js';

const containerSelector = '.collection-ajax-container';
const contentSelector = '.collection-ajax-content';

function getContainer() {
    return document.querySelector(containerSelector);
}

function getContent() {
    return document.querySelector(contentSelector);
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function isSeriesPageUrl(url) {
    const pathname = typeof url === 'string' ? new URL(url).pathname : url.pathname;
    return /\/manga\/series($|\/page\/\d+$)/.test(pathname);
}

async function fetchHtml(ajaxUrl) {
    const cached = getPrefetchedPage(ajaxUrl);
    if (cached) return cached;

    const response = await fetch(`${ajaxUrl}?t=${Date.now()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    if (!response.ok) throw new Error('Erreur AJAX');
    return await response.text();
}

export async function loadSeriesPage(href, pushState = true) {
    const container = getContainer();
    const content = getContent();
    if (!container || !content) return;

    container.classList.add('is-loading');

    try {
        const ajaxUrl = buildAjaxUrl(href);
        const html = await fetchHtml(ajaxUrl);

        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector(contentSelector);

        if (!newContent) throw new Error('[AJAX] New content not found');

        // Remplace uniquement le HTML
        content.innerHTML = newContent.innerHTML;

        if (pushState) window.history.pushState({}, '', href);

        document.dispatchEvent(new CustomEvent('ajax:series-loaded'));
        scrollToTop();

        // Préfetch page suivante
        const active = document.querySelector('.collection-pagination-link.active');
        const next = active?.nextElementSibling;
        if (next?.classList.contains('collection-pagination-link')) {
            prefetchSeriesPage(next.href);
        }

    } catch (error) {
        console.error('[AJAX] loadSeriesPage failed', error);
    } finally {
        container.classList.remove('is-loading');
    }
}

async function handleClick(e) {
    const target = e.target;
    if (!(target instanceof Element)) return;

    const link = target.closest('a.collection-pagination-link, a.collection-card-link');
    if (!link) return;
    if (link.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey) return;

    e.preventDefault();
    await loadSeriesPage(link.href);
}

async function handlePopState() {
    const href = window.location.href;
    if (!isSeriesPageUrl(href)) return;
    await loadSeriesPage(href, false);
}

export function initLoadSeriesPage() {
    if (document.body.dataset.loadSeriesPageInit === 'true') return;
    document.body.dataset.loadSeriesPageInit = 'true';

    if (!getContainer()) return;

    document.addEventListener('click', handleClick);
    window.addEventListener('popstate', handlePopState);

    if (isSeriesPageUrl(window.location.href)) {
        loadSeriesPage(window.location.href, false);
    }
}