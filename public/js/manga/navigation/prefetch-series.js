// ==================================================
// Prefetch Series
// ==================================================

const prefetchedPages = new Map();

/* ----------------- Helpers ----------------- */

function getBasePath() {
    return '/lolissr';
}

/* ----------------- Build AJAX URL ----------------- */

export function buildAjaxUrl(link) {

    const href = link.href ?? link;

    const url = new URL(
        href,
        window.location.origin
    );

    const match = url.pathname.match(
        /\/manga\/series\/page\/(\d+)$/
    );

    const page = match
        ? Math.max(
            1,
            parseInt(match[1], 10)
        )
        : 1;

    url.pathname =
        `${getBasePath()}/manga/ajax/series/page/${page}`;

    return url.toString();
}

/* ----------------- Prefetch HTML ----------------- */

export async function prefetchSeriesPage(url) {

    const ajaxUrl = buildAjaxUrl(url);

    if (prefetchedPages.has(ajaxUrl)) {
        return prefetchedPages.get(ajaxUrl);
    }

    try {

        const response = await fetch(
            ajaxUrl,
            {
                headers: {
                    'X-Requested-With':
                        'XMLHttpRequest'
                }
            }
        );

        if (!response.ok) {
            return null;
        }

        const html = await response.text();

        prefetchedPages.set(
            ajaxUrl,
            html
        );

        return html;

    } catch (error) {

        console.error(
            '[PREFETCH] failed',
            error
        );

        return null;
    }
}

/* ----------------- Cache ----------------- */

export function getPrefetchedPage(url) {

    return prefetchedPages.get(url);
}

/* ----------------- Init ----------------- */

export function initPrefetchSeries() {

    if (
        document.body.dataset
            .prefetchSeriesInit === 'true'
    ) {
        return;
    }

    document.body.dataset
        .prefetchSeriesInit = 'true';

    // Préfetch pagination hover
    document.addEventListener(
        'pointerenter',
        event => {

            if (!(event.target instanceof Element)) {
                return;
            }

            const link = event.target.closest(
                '.collection-pagination-link'
            );

            if (!link) {
                return;
            }

            prefetchSeriesPage(link.href);

        },
        true
    );
}