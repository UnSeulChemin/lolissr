import { showToast } from '../../core/toast.js';

const seriesPageCache = new Map();
const containerSelector = '.collection-ajax-container';
const contentSelector = '.collection-ajax-content';

const getContainer = () => document.querySelector(containerSelector);
const getContent = () => document.querySelector(contentSelector);
const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });
const isSeriesPage = () => /\/manga\/series($|\/page\/\d+$)/.test(window.location.pathname);

function buildAjaxUrl(link) {
    const url = new URL(link.href, window.location.origin);
    const match = url.pathname.match(/\/manga\/series\/page\/(\d+)$/);
    const page = match ? Math.max(1, parseInt(match[1], 10)) : 1;
    url.pathname = `/lolissr/manga/ajax/series/page/${page}`;
    return url.toString();
}

async function fetchHtml(url) {
    if (seriesPageCache.has(url)) return seriesPageCache.get(url);
    try {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const html = res.ok ? await res.text() : '<p class="collection-empty">Aucune série sur cette page.</p>';
        seriesPageCache.set(url, html);
        return html;
    } catch {
        return '<p class="collection-empty">Erreur réseau.</p>';
    }
}

async function loadSeries(url, fallback) {
    const container = getContainer();
    const content = getContent();
    if (!container || !content) return;

    container.classList.add('is-loading');
    try {
        const html = await fetchHtml(url);
        content.innerHTML = html;
        scrollToTop();
    } catch (err) {
        console.error(err);
        content.innerHTML = '<p class="collection-empty">Erreur AJAX.</p>';
        window.location.href = fallback;
    } finally {
        container.classList.remove('is-loading');
    }
}

export function initLoadSeriesPage() {
    const container = getContainer();
    if (!container) return;

    // Click pagination
    document.addEventListener('click', async e => {
        const link = e.target.closest('.collection-pagination-link');
        if (!link || !container.contains(link)) return;
        e.preventDefault();
        const ajaxUrl = buildAjaxUrl(link);
        await loadSeries(ajaxUrl, link.href);
        history.pushState({ ajaxUrl }, '', link.href);
    });

    // Navigation navigateur
    window.addEventListener('popstate', async () => {
        if (!isSeriesPage()) return;
        await loadSeries(window.location.href, window.location.href);
    });
}