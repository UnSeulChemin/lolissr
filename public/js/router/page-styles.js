// Load inactive styles before replacing the page, then commit them synchronously.
// Each navigation owns its pending links so cancellation cannot affect another one.
export async function preparePageStyles(stylesheets, signal)
{
    if (!Array.isArray(stylesheets))
    {
        throw new Error('Page stylesheet dependencies are missing.');
    }

    const urls = [...new Set(stylesheets.map(href =>
    {
        if (typeof href !== 'string')
        {
            throw new Error('Invalid page stylesheet.');
        }

        const url = new URL(href, window.location.origin);

        if (url.origin !== window.location.origin || !url.pathname.endsWith('.css'))
        {
            throw new Error('Invalid page stylesheet URL.');
        }

        return url.href;
    }))];

    const active = [...document.querySelectorAll('link[data-page-style]')];
    const created = [];
    const links = urls.map(href =>
    {
        const existing = active.find(link => link.href === href && link.sheet);

        if (existing)
        {
            return existing;
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.media = 'not all';
        created.push(link);
        return link;
    });

    let rejectLoad;
    const removePending = () => created.forEach(link => link.remove());
    const abort = () =>
    {
        removePending();
        rejectLoad?.(new DOMException('Navigation aborted', 'AbortError'));
    };

    signal?.addEventListener('abort', abort, {once: true});

    let timer;

    try
    {
        await new Promise((resolve, reject) =>
        {
            rejectLoad = reject;

            if (signal?.aborted)
            {
                abort();
                return;
            }

            if (created.length === 0)
            {
                resolve();
                return;
            }

            let remaining = created.length;
            timer = window.setTimeout(() => reject(new Error('Page stylesheet loading timed out.')), 10000);

            for (const link of created)
            {
                link.onload = () =>
                {
                    if (--remaining === 0)
                    {
                        resolve();
                    }
                };
                link.onerror = () => reject(new Error(`Unable to load stylesheet: ${link.href}`));
                document.head.append(link);
            }
        });
    }
    catch (error)
    {
        removePending();
        signal?.removeEventListener('abort', abort);
        throw error;
    }
    finally
    {
        window.clearTimeout(timer);

        for (const link of created)
        {
            link.onload = null;
            link.onerror = null;
        }
    }

    return () =>
    {
        signal?.removeEventListener('abort', abort);

        if (signal?.aborted)
        {
            removePending();
            throw new DOMException('Navigation aborted', 'AbortError');
        }

        for (const link of document.querySelectorAll('link[data-page-style]'))
        {
            if (!links.includes(link))
            {
                link.remove();
            }
        }

        // Appending in manifest order also preserves the CSS cascade on back/forward.
        for (const link of links)
        {
            link.setAttribute('data-page-style', '');
            link.media = 'all';
            document.head.append(link);
        }
    };
}
