import { showToast } from '../../core/toast.js';

const seriesPageCache = new Map();

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
        behavior: 'smooth'
    });
}

function isSeriesPage()
{
    return (
        /\/manga\/series$/.test(
            window.location.pathname
        )
        || /\/manga\/series\/\d+$/.test(
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

    url.pathname = url.pathname.replace(
        '/manga/series/',
        '/manga/ajax/series/'
    );

    if (/\/manga\/series$/.test(url.pathname))
    {
        url.pathname =
            '/manga/ajax/series/1';
    }

    return url.toString();
}

function getCurrentSeriesAjaxUrl()
{
    const url = new URL(
        window.location.href
    );

    if (/\/manga\/series$/.test(url.pathname))
    {
        url.pathname =
            '/manga/ajax/series/1';

        return url.toString();
    }

    url.pathname = url.pathname.replace(
        '/manga/series/',
        '/manga/ajax/series/'
    );

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
                        'XMLHttpRequest'
                }
            }
        );

        if (!response.ok)
        {
            return;
        }

        const html = await response.text();

        seriesPageCache.set(
            ajaxUrl,
            html
        );
    }
    catch
    {
        // preload silencieux
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

    prefetchSeriesPage(nextAjaxUrl);
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
                    'XMLHttpRequest'
            }
        }
    );

    if (!response.ok)
    {
        throw new Error(
            'Erreur AJAX pagination'
        );
    }

    const html = await response.text();

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

        seriesContent.innerHTML = html;

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

    if (
        document.body.dataset
            .loadSeriesPageInit === 'true'
    )
    {
        return;
    }

    document.body.dataset
        .loadSeriesPageInit = 'true';

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

            const seriesContainer =
                getSeriesContainer();

            if (
                !seriesContainer
                || !seriesContainer.contains(
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
                        paginationLink.href
                },
                '',
                paginationLink.href
            );
        }
    );

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
                    /\/manga\/series\/(\d+)$/
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

    prefetchNextSeriesPage();
}