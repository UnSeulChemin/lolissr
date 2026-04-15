export function initPaginationAjax()
{
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

        try
        {
            const ajaxUrl = link.href.replace(
                '/manga/collection/page/',
                '/manga/collection-ajax/page/'
            );

            container.style.opacity = '0.45';

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

            container.innerHTML = html;

            container.style.opacity = '1';

            history.pushState({}, '', link.href);

            window.scrollTo({
                top: container.offsetTop - 30,
                behavior: 'smooth'
            });
        }
        catch (error)
        {
            container.style.opacity = '1';

            // fallback normal
            window.location.href = link.href;
        }
    });
}