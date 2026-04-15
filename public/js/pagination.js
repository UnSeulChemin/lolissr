import { showToast } from './toast.js';

const paginationCache = new Map();

async function preloadPage(url)
{
    if (paginationCache.has(url))
    {
        return;
    }

    try
    {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok)
        {
            return;
        }

        const html = await response.text();

        paginationCache.set(url, html);
    }
    catch (error)
    {
        // preload silencieux
    }
}

function buildAjaxUrl(link)
{
    return link.href.replace(
        '/manga/collection/page/',
        '/manga/collection-ajax/page/'
    );
}

export function initPaginationAjax()
{
    document.addEventListener('mouseover', (event) =>
    {
        const link = event.target.closest('.collection-pagination-link');

        if (!link)
        {
            return;
        }

        preloadPage(buildAjaxUrl(link));
    });

    document.addEventListener('focusin', (event) =>
    {
        const link = event.target.closest('.collection-pagination-link');

        if (!link)
        {
            return;
        }

        preloadPage(buildAjaxUrl(link));
    });

    document.addEventListener('click', async (event) =>
    {
        const link = event.target.closest('.collection-pagination-link');

        if (!link)
        {
            return;
        }

        const container = document.querySelector('.collection-ajax-container');

        if (!container)
        {
            return;
        }

        event.preventDefault();

        const ajaxUrl = buildAjaxUrl(link);

        try
        {
            container.classList.add('is-loading');

            let html = paginationCache.get(ajaxUrl);

            if (!html)
            {
                const response = await fetch(ajaxUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok)
                {
                    throw new Error('Erreur AJAX pagination');
                }

                html = await response.text();
                paginationCache.set(ajaxUrl, html);
            }

            container.innerHTML = html;

            history.pushState(
                {
                    ajaxUrl: ajaxUrl,
                    pageUrl: link.href
                },
                '',
                link.href
            );

            window.scrollTo({
                top: container.offsetTop - 30,
                behavior: 'smooth'
            });
        }
        catch (error)
        {
            showToast('Erreur chargement page', 'error');
            window.location.href = link.href;
        }
        finally
        {
            container.classList.remove('is-loading');
        }
    });

    window.addEventListener('popstate', async () =>
    {
        const container = document.querySelector('.collection-ajax-container');

        if (!container)
        {
            return;
        }

        const match = window.location.pathname.match(/\/manga\/collection\/page\/(\d+)$/);

        if (!match)
        {
            return;
        }

        const page = match[1];
        const ajaxUrl = `${window.location.origin}${window.location.pathname.replace(
            '/manga/collection/page/',
            '/manga/collection-ajax/page/'
        )}`;

        try
        {
            container.classList.add('is-loading');

            let html = paginationCache.get(ajaxUrl);

            if (!html)
            {
                const response = await fetch(ajaxUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok)
                {
                    throw new Error('Erreur AJAX pagination');
                }

                html = await response.text();
                paginationCache.set(ajaxUrl, html);
            }

            container.innerHTML = html;

            window.scrollTo({
                top: container.offsetTop - 30,
                behavior: 'smooth'
            });
        }
        catch (error)
        {
            showToast(`Erreur chargement page ${page}`, 'error');
            window.location.reload();
        }
        finally
        {
            container.classList.remove('is-loading');
        }
    });
}