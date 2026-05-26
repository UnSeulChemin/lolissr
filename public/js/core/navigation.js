// =========================================
// CORE : NAVIGATION
// =========================================

export function normalizeUrl(href)
{
    const url = new URL(href, window.location.origin);

    let pathname = url.pathname;

    // force trailing slash propre
    if (!pathname.endsWith('/') && !pathname.includes('.')) {
        pathname += '/';
    }

    // ignore hash (IMPORTANT SPA)
    const search = url.search || '';

    return pathname + search;
}

export function shouldIgnoreLink(link)
{
    if (!(link instanceof HTMLAnchorElement)) return true;
    if (!link.href) return true;

    const url = new URL(link.href, window.location.origin);

    if (url.origin !== window.location.origin) return true;
    if (link.target === '_blank') return true;
    if (link.hasAttribute('download')) return true;
    if (link.dataset.noAjax !== undefined) return true;

    if (
        url.hash &&
        url.pathname === location.pathname
    ) {
        return true;
    }

    if (/\.(jpg|jpeg|png|gif|webp|svg|pdf|zip)$/i.test(url.pathname)) {
        return true;
    }

    return false;
}