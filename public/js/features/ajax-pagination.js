import { showToast } from '../core/toast.js';

const collectionPaginationHtmlCache = new Map();

function getCollectionPaginationLink(target)
{
    return target?.closest('.collection-pagination-link') ?? null;
}

function getCollectionAjaxContainer()
{
    return document.querySelector('.collection-ajax-container');
}

function getCollectionAjaxContent()
{
    return document.querySelector('.collection-ajax-content');
}

function scrollCollectionPaginationToTop()
{
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

function isCollectionListingPage()
{
    return (
        /\/manga\/collection$/.test(window.location.pathname)
        || /\/manga\/collection\/page\/\d+$/.test(window.location.pathname)
    );
}

function buildCollectionPaginationAjaxUrl(link)
{
    const url = new URL(link.href, window.location.origin);

    url.pathname = url.pathname.replace(
        '/manga/collection/page/',
        '/manga/collection-ajax/page/'
    );

    if (/\/manga\/collection$/.test(url.pathname))
    {
        url.pathname = url.pathname.replace(
            '/manga/collection',
            '/manga/collection-ajax/page/1'
        );
    }

    return url.toString();
}

function getCurrentCollectionPaginationAjaxUrl()
{
    const url = new URL(window.location.href);

    if (/\/manga\/collection$/.test(url.pathname))
    {
        url.pathname = url.pathname.replace(
            '/manga/collection',
            '/manga/collection-ajax/page/1'
        );

        return url.toString();
    }

    url.pathname = url.pathname.replace(
        '/manga/collection/page/',
        '/manga/collection-ajax/page/'
    );

    return url.toString();
}

async function prefetchCollectionPaginationPage(ajaxUrl)
{
    if (!ajaxUrl || collectionPaginationHtmlCache.has(ajaxUrl))
    {
        return;
    }

    try
    {
        const response = await fetch(ajaxUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok)
        {
            return;
        }

        const html = await response.text();

        collectionPaginationHtmlCache.set(ajaxUrl, html);
    }
    catch (error)
    {
        // preload silencieux
    }
}

function prefetchNextCollectionPaginationPage()
{
    const ajaxContainer = getCollectionAjaxContainer();

    if (!ajaxContainer)
    {
        return;
    }

    const activePaginationLink = ajaxContainer.querySelector(
        '.collection-pagination-link.active'
    );

    if (!activePaginationLink)
    {
        return;
    }

    const nextPaginationLink = activePaginationLink.nextElementSibling;

    if (!nextPaginationLink?.classList.contains('collection-pagination-link'))
    {
        return;
    }

    const nextAjaxUrl = buildCollectionPaginationAjaxUrl(nextPaginationLink);

    prefetchCollectionPaginationPage(nextAjaxUrl);
}

async function fetchCollectionPaginationHtml(ajaxUrl)
{
    const cachedHtml = collectionPaginationHtmlCache.get(ajaxUrl);

    if (cachedHtml)
    {
        return cachedHtml;
    }

    const response = await fetch(ajaxUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    if (!response.ok)
    {
        throw new Error('Erreur AJAX pagination');
    }

    const html = await response.text();

    collectionPaginationHtmlCache.set(ajaxUrl, html);

    return html;
}

async function loadCollectionPaginationContent(
    ajaxUrl,
    fallbackUrl,
    errorMessage,
    shouldScrollToTop = true
)
{
    const ajaxContainer = getCollectionAjaxContainer();
    const ajaxContent = getCollectionAjaxContent();

    if (!ajaxContainer || !ajaxContent)
    {
        window.location.href = fallbackUrl;
        return false;
    }

    try
    {
        ajaxContainer.classList.add('is-loading');

        const html = await fetchCollectionPaginationHtml(ajaxUrl);

        ajaxContent.innerHTML = html;

        prefetchNextCollectionPaginationPage();

        if (shouldScrollToTop)
        {
            requestAnimationFrame(() =>
            {
                scrollCollectionPaginationToTop();
            });
        }

        return true;
    }
    catch (error)
    {
        showToast(errorMessage, 'error');
        window.location.href = fallbackUrl;

        return false;
    }
    finally
    {
        ajaxContainer.classList.remove('is-loading');
    }
}

export function initCollectionPaginationAjax()
{
    const ajaxContainer = getCollectionAjaxContainer();

    if (!ajaxContainer)
    {
        return;
    }

    if (document.body.dataset.collectionPaginationAjaxInit === 'true')
    {
        return;
    }

    document.body.dataset.collectionPaginationAjaxInit = 'true';

    document.addEventListener('pointerover', (event) =>
    {
        if (event.pointerType && event.pointerType !== 'mouse')
        {
            return;
        }

        const paginationLink = getCollectionPaginationLink(event.target);

        if (!paginationLink)
        {
            return;
        }

        const ajaxUrl = buildCollectionPaginationAjaxUrl(paginationLink);

        prefetchCollectionPaginationPage(ajaxUrl);
    });

    document.addEventListener('focusin', (event) =>
    {
        const paginationLink = getCollectionPaginationLink(event.target);

        if (!paginationLink)
        {
            return;
        }

        const ajaxUrl = buildCollectionPaginationAjaxUrl(paginationLink);

        prefetchCollectionPaginationPage(ajaxUrl);
    });

    document.addEventListener('click', async (event) =>
    {
        const paginationLink = getCollectionPaginationLink(event.target);

        if (!paginationLink)
        {
            return;
        }

        const ajaxContainer = getCollectionAjaxContainer();

        if (!ajaxContainer || !ajaxContainer.contains(paginationLink))
        {
            return;
        }

        event.preventDefault();

        const ajaxUrl = buildCollectionPaginationAjaxUrl(paginationLink);

        const success = await loadCollectionPaginationContent(
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
                ajaxUrl: ajaxUrl,
                pageUrl: paginationLink.href
            },
            '',
            paginationLink.href
        );
    });

    window.addEventListener('popstate', async () =>
    {
        if (!isCollectionListingPage())
        {
            return;
        }

        const pageMatch = window.location.pathname.match(
            /\/manga\/collection\/page\/(\d+)$/
        );

        const currentPageNumber = pageMatch ? pageMatch[1] : '1';

        await loadCollectionPaginationContent(
            getCurrentCollectionPaginationAjaxUrl(),
            window.location.href,
            `Erreur chargement page ${currentPageNumber}`,
            false
        );
    });

    prefetchNextCollectionPaginationPage();
}