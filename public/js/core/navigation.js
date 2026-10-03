// =================================================
// SOCLE : NAVIGATION
// =================================================

// =================================================
// NORMALISATION DE L’URL DE ROUTE
// =================================================

export function normalizeRouteUrl(href)
{
    const url = new URL(href, window.location.origin);

    let pathname = url.pathname.replace(/\/+/g, '/');

    if (pathname === '')
    {
        pathname = '/';
    }

    url.pathname = pathname;

    return url.toString();
}

// =================================================
// NORMALISATION DE LA CLÉ DE CACHE
// =================================================

export function normalizeCacheKey(href)
{
    const url = new URL(normalizeRouteUrl(href));

    url.hash = '';

    return url.toString();
}

// =================================================
// IGNORER LIEN
// =================================================

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

    const url = new URL(link.href, window.location.origin);

    // =================================================
    // EXTERNE
    // =================================================

    if (url.origin !== window.location.origin)
    {
        return true;
    }

    // =================================================
    // CIBLE
    // =================================================

    if (link.target === '_blank')
    {
        return true;
    }

    // =================================================
    // TÉLÉCHARGEMENT
    // =================================================

    if (link.hasAttribute('download'))
    {
        return true;
    }

    // =================================================
    // ROUTEUR DÉSACTIVÉ
    // =================================================

    if (link.dataset.noRouter !== undefined)
    {
        return true;
    }

    // =================================================
    // ANCRE DE LA PAGE ACTUELLE
    // =================================================

    if (url.hash && normalizeCacheKey(url.href) === normalizeCacheKey(location.href))
    {
        return true;
    }

    // =================================================
    // FICHIERS STATIQUES
    // =================================================

    if (/\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|mp4|webm)$/i.test(url.pathname))
    {
        return true;
    }

    return false;
}