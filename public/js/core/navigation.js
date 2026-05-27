// =========================================
// CORE : NAVIGATION
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
    // CLEAN PATH
    // =====================================

    let pathname =
        url.pathname.replace(
            /\/+/g,
            '/',
        );

    // =====================================
    // TRAILING SLASH
    // =====================================

    if (
        !pathname.endsWith('/')
        && !pathname.includes('.')
    ) {

        pathname += '/';
    }

    // =====================================
    // REMOVE HASH
    // =====================================

    url.hash = '';

    // =====================================
    // APPLY
    // =====================================

    url.pathname =
        pathname;

    return url.toString();
}

// =========================================
// LINK FILTER
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
        link.target
        === '_blank'
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
    // SAME PAGE HASH
    // =====================================

    if (
        url.hash
        && url.pathname
        === location.pathname
    ) {
        return true;
    }

    // =====================================
    // STATIC FILES
    // =====================================

    if (
        /\.(jpg|jpeg|png|gif|webp|svg|pdf|zip)$/i
            .test(
                url.pathname,
            )
    ) {
        return true;
    }

    return false;
}