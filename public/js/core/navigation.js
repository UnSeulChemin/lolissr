// =========================================
// CORE : NAVIGATION
// =========================================

// =========================================
// NORMALIZE ROUTE URL
// =========================================

export function normalizeRouteUrl(href)
{
    const url = new URL(
        href,
        window.location.origin,
    );

    let pathname = url.pathname.replace(
        /\/+/g,
        '/',
    );

    if (pathname === '')
    {
        pathname = '/';
    }

    url.pathname = pathname;

    return url.toString();
}

// =========================================
// NORMALIZE CACHE KEY
// =========================================

export function normalizeCacheKey(href)
{
    const url = new URL(
        normalizeRouteUrl(href),
    );

    url.hash = '';

    return url.toString();
}

// =========================================
// IGNORE LINK
// =========================================

export function shouldIgnoreLink(link)
{
    if (! (link instanceof HTMLAnchorElement))
    {
        return true;
    }

    if (! link.href)
    {
        return true;
    }

    const url = new URL(
        link.href,
        window.location.origin,
    );

    // =====================================
    // EXTERNAL
    // =====================================

    if (url.origin !== window.location.origin)
    {
        return true;
    }

    // =====================================
    // TARGET
    // =====================================

    if (link.target === '_blank')
    {
        return true;
    }

    // =====================================
    // DOWNLOAD
    // =====================================

    if (link.hasAttribute('download'))
    {
        return true;
    }

    // =====================================
    // ROUTER DISABLED
    // =====================================

    if (link.dataset.noRouter !== undefined)
    {
        return true;
    }

    // =====================================
    // SAME PAGE HASH
    // =====================================

    if (
        url.hash
        && normalizeCacheKey(url.href) === normalizeCacheKey(location.href)
    )
    {
        return true;
    }

    // =====================================
    // STATIC FILES
    // =====================================

    if (/\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|mp4|webm)$/i.test(url.pathname))
    {
        return true;
    }

    return false;
}