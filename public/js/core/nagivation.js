// =========================================
// CORE : NAVIGATION
// =========================================

/**
 * Normalize URL
 */
export function normalizeUrl(
    href,
)
{
    const url =
        new URL(
            href,
            window.location.origin,
        );

    let pathname =
        url.pathname;

    if (
        !pathname.endsWith('/')
        && !pathname.includes('.')
    ) {

        pathname += '/';
    }

    return (
        pathname
        + url.search
    );
}

/**
 * Ignore Link
 */
export function shouldIgnoreLink(
    link,
)
{
    if (
        !(link instanceof HTMLAnchorElement)
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
    // External
    // =====================================

    if (
        url.origin
        !== window.location.origin
    ) {
        return true;
    }

    // =====================================
    // Same-page hash
    // =====================================

    if (
        url.hash
        && normalizeUrl(
            url.href,
        ) === normalizeUrl(
            window.location.href,
        )
    ) {
        return true;
    }

    // =====================================
    // New tab
    // =====================================

    if (
        link.target
        === '_blank'
    ) {
        return true;
    }

    // =====================================
    // Download
    // =====================================

    if (
        link.hasAttribute(
            'download',
        )
    ) {
        return true;
    }

    // =====================================
    // AJAX opt-out
    // =====================================

    if (
        link.dataset.noAjax
        !== undefined
    ) {
        return true;
    }

    // =====================================
    // Static files
    // =====================================

    if (
        /\.(jpg|jpeg|png|gif|webp|svg|pdf|zip)$/i
            .test(url.pathname)
    ) {
        return true;
    }

    return false;
}