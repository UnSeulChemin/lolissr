// =========================================
// CORE : NAVIGATION
// =========================================

// =========================================
// NORMALIZE URL
// =========================================

export function normalizeUrl(
    href,
)
{
    const url =
        new URL(
            href,
            window.location.origin,
        );

    // =====================================
    // REMOVE HASH
    // =====================================

    url.hash =
        '';

    // =====================================
    // CLEAN PATH
    // =====================================

    let pathname =
        url.pathname
            .replace(
                /\/+/g,
                '/',
            );

    // =====================================
    // FORCE TRAILING SLASH
    // =====================================

    if (
        !pathname.endsWith('/')
        && !pathname.includes('.')
    ) {

        pathname += '/';
    }

    // =====================================
    // ROOT
    // =====================================

    if (
        pathname === ''
    ) {

        pathname =
            '/';
    }

    url.pathname =
        pathname;

    return url.toString();
}

// =========================================
// IGNORE LINK
// =========================================

export function shouldIgnoreLink(
    link,
)
{
    if (
        !(
            link
            instanceof HTMLAnchorElement
        )
    ) {
        return true;
    }

    if (!link.href) {
        return true;
    }

    const url =
        new URL(
            link.href,
            window.location.origin,
        );

    // =====================================
    // EXTERNAL
    // =====================================

    if (
        url.origin
        !== window.location.origin
    ) {
        return true;
    }

    // =====================================
    // TARGET
    // =====================================

    if (
        link.target === '_blank'
    ) {
        return true;
    }

    // =====================================
    // DOWNLOAD
    // =====================================

    if (
        link.hasAttribute(
            'download',
        )
    ) {
        return true;
    }

    // =====================================
    // AJAX DISABLED
    // =====================================

    if (
        link.dataset.noAjax
        !== undefined
    ) {
        return true;
    }

    // =====================================
    // SAME HASH
    // =====================================

    if (
        url.hash
        && normalizeUrl(
            url.pathname,
        )
        === normalizeUrl(
            location.pathname,
        )
    ) {
        return true;
    }

    // =====================================
    // STATIC FILES
    // =====================================

    if (
        /\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|mp4|webm)$/i
            .test(
                url.pathname,
            )
    ) {
        return true;
    }

    return false;
}