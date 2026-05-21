import { showToast } from '../../core/toast.js';

const seriesPageCache = new Map();

const basePath = (
    document.body.dataset.basePath
    ?? '/'
).replace(/\/+$/, '/');

function getSeriesPaginationLink(target)
{
    return target?.closest(
        '.collection-pagination-link'
    ) ?? null;
}

function getSeriesContainer()
{
    return document.querySelector(
        '.collection-ajax-container'
    );
}

function getSeriesContent()
{
    return document.querySelector(
        '.collection-ajax-content'
    );
}

function scrollSeriesToTop()
{
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
}

function isSeriesPage()
{
    return (
        /\/manga\/series$/.test(
            window.location.pathname
        )
        || /\/manga\/series\/page\/\d+$/.test(
            window.location.pathname
        )
    );
}

function buildSeriesAjaxUrl(link)
{
    const url = new URL(
        link.href,
        window.location.origin
    );

    /*
    |------------------------------------------------------------------
    | /manga/series/page/2
    | -> /manga/ajax/series/page/2
    |------------------------------------------------------------------
    */

    const match = url.pathname.match(
        /\/manga\/series\/page\/(\d+)$/
    );

    if (match)
    {
        url.pathname =
            `${basePath}manga/ajax/series/page/${match[1]}`;

        return url.toString();
    }

    /*
    |------------------------------------------------------------------
    | /manga/series
    | -> /manga/ajax/series/page/1
    |------------------------------------------------------------------
    */

    if (/\/manga\/series$/.test(url.pathname))
    {
        url.pathname =
            `${basePath}manga/ajax/series/page/1`;
    }

    return url.toString();
}

function getCurrentSeriesAjaxUrl()
{
    const url = new URL(
        window.location.href
    );

    const match = url.pathname.match(
        /\/manga\/series\/page\/(\d+)$/
    );

    /*
    |------------------------------------------------------------------
    | Page courante pagination
    |------------------------------------------------------------------
    */

    if (match)
    {
        url.pathname =
            `${basePath}manga/ajax/series/page/${match[1]}`;

        return url.toString();
    }

    /*
    |------------------------------------------------------------------
    | Première page
    |------------------------------------------------------------------
    */

    url.pathname =
        `${basePath}manga/ajax/series/page/1`;

    return url.toString();
}

async function prefetchSeriesPage(ajaxUrl)
{
    if (
        !ajaxUrl
        || seriesPageCache.has(ajaxUrl)
    )
    {
        return;
    }

    try
    {
        const response = await fetch(
            ajaxUrl,
            {
                headers:
                {
                    'X-Requested-With':
                        'XMLHttpRequest',
                },
            }
        );

        if (!response.ok)
        {
            return;
        }

        const html =
            await response.text();

        seriesPageCache.set(
            ajaxUrl,
            html
        );
    }
    catch
    {
        /*
        |--------------------------------------------------------------
        | Prefetch silencieux
        |--------------------------------------------------------------
        */
    }
}

function prefetchNextSeriesPage()
{
    const seriesContainer =
        getSeriesContainer();

    if (!seriesContainer)
    {
        return;
    }

    const activePaginationLink =
        seriesContainer.querySelector(
            '.collection-pagination-link.active'
        );

    if (!activePaginationLink)
    {
        return;
    }

    const nextPaginationLink =
        activePaginationLink.nextElementSibling;

    if (
        !nextPaginationLink?.classList.contains(
            'collection-pagination-link'
        )
    )
    {
        return;
    }

    const nextAjaxUrl =
        buildSeriesAjaxUrl(
            nextPaginationLink
        );

    prefetchSeriesPage(
        nextAjaxUrl
    );
}

async function fetchSeriesHtml(ajaxUrl)
{
    const cachedHtml =
        seriesPageCache.get(ajaxUrl);

    if (cachedHtml)
    {
        return cachedHtml;
    }

    const response = await fetch(
        ajaxUrl,
        {
            headers:
            {
                'X-Requested-With':
                    'XMLHttpRequest',
            },
        }
    );

    if (!response.ok)
    {
        throw new Error(
            'Erreur AJAX pagination'
        );
    }

    const html =
        await response.text();

    seriesPageCache.set(
        ajaxUrl,
        html
    );

    return html;
}

async function loadSeriesContent(
    ajaxUrl,
    fallbackUrl,
    errorMessage,
    shouldScrollToTop = true
)
{
    const seriesContainer =
        getSeriesContainer();

    const seriesContent =
        getSeriesContent();

    if (
        !seriesContainer
        || !seriesContent
    )
    {
        window.location.href =
            fallbackUrl;

        return false;
    }

    try
    {
        seriesContainer.classList.add(
            'is-loading'
        );

        const html =
            await fetchSeriesHtml(
                ajaxUrl
            );

        seriesContent.innerHTML =
            html;

        prefetchNextSeriesPage();

        if (shouldScrollToTop)
        {
            requestAnimationFrame(() =>
            {
                scrollSeriesToTop();
            });
        }

        return true;
    }
    catch
    {
        showToast(
            errorMessage,
            'error'
        );

        window.location.href =
            fallbackUrl;

        return false;
    }
    finally
    {
        seriesContainer.classList.remove(
            'is-loading'
        );
    }
}

export function initLoadSeriesPage()
{
    const seriesContainer =
        getSeriesContainer();

    if (!seriesContainer)
    {
        return;
    }

    /*
    |------------------------------------------------------------------
    | Sécurité double init
    |------------------------------------------------------------------
    */

    if (
        document.body.dataset
            .loadSeriesPageInit === 'true'
    )
    {
        return;
    }

    document.body.dataset
        .loadSeriesPageInit = 'true';

    /*
    |------------------------------------------------------------------
    | Prefetch hover souris
    |------------------------------------------------------------------
    */

    document.addEventListener(
        'pointerover',
        (event) =>
        {
            if (
                event.pointerType
                && event.pointerType !== 'mouse'
            )
            {
                return;
            }

            const paginationLink =
                getSeriesPaginationLink(
                    event.target
                );

            if (!paginationLink)
            {
                return;
            }

            const ajaxUrl =
                buildSeriesAjaxUrl(
                    paginationLink
                );

            prefetchSeriesPage(
                ajaxUrl
            );
        }
    );

    /*
    |------------------------------------------------------------------
    | Focus clavier
    |------------------------------------------------------------------
    */

    document.addEventListener(
        'focusin',
        (event) =>
        {
            const paginationLink =
                getSeriesPaginationLink(
                    event.target
                );

            if (!paginationLink)
            {
                return;
            }

            const ajaxUrl =
                buildSeriesAjaxUrl(
                    paginationLink
                );

            prefetchSeriesPage(
                ajaxUrl
            );
        }
    );

    /*
    |------------------------------------------------------------------
    | Pagination AJAX
    |------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        async (event) =>
        {
            const paginationLink =
                getSeriesPaginationLink(
                    event.target
                );

            if (!paginationLink)
            {
                return;
            }

            if (
                !seriesContainer.contains(
                    paginationLink
                )
            )
            {
                return;
            }

            event.preventDefault();

            const ajaxUrl =
                buildSeriesAjaxUrl(
                    paginationLink
                );

            const success =
                await loadSeriesContent(
                    ajaxUrl,
                    paginationLink.href,
                    'Erreur chargement page',
                    true
                );

            if (!success)
            {
                return;
            }

            history.pushState(
                {
                    ajaxUrl,
                    pageUrl:
                        paginationLink.href,
                },
                '',
                paginationLink.href
            );
        }
    );

    /*
    |------------------------------------------------------------------
    | Navigation navigateur
    |------------------------------------------------------------------
    */

    window.addEventListener(
        'popstate',
        async () =>
        {
            if (!isSeriesPage())
            {
                return;
            }

            const pageMatch =
                window.location.pathname.match(
                    /\/manga\/series\/page\/(\d+)$/
                );

            const currentPageNumber =
                pageMatch
                    ? pageMatch[1]
                    : '1';

            await loadSeriesContent(
                getCurrentSeriesAjaxUrl(),
                window.location.href,
                `Erreur chargement page ${currentPageNumber}`,
                false
            );
        }
    );

    /*
    |------------------------------------------------------------------
    | Prefetch page suivante
    |------------------------------------------------------------------
    */

    prefetchNextSeriesPage();

    /*
    |------------------------------------------------------------------
    | Remove skeleton initial
    |------------------------------------------------------------------
    */

    requestAnimationFrame(() =>
    {
        seriesContainer.classList.remove(
            'is-loading'
        );
    });
}