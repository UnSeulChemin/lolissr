export async function runBrowserScenario()
{
    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const {initPrefetch} = await import('./js/router/prefetch/prefetch-init.js');
    const {config} = await import('./js/core/config.js');
    const {NAVIGATION_START} = await import('./js/core/navigation-protocol.js');
    const originalFetch = window.fetch;
    let requests = 0;
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const wait = () => new Promise(resolve => setTimeout(resolve, config.prefetch.hoverDelay + 50));
    window.fetch = async () =>
    {
        requests++;
        return new Response(JSON.stringify({success: true, type: 'page', page: {html: '<main></main>'}}), {headers: {'Content-Type': 'application/json'}});
    };
    initPrefetch();
    initPrefetch();
    const link = document.createElement('a');
    link.dataset.prefetch = '';
    document.body.append(link);
    const enter = path =>
    {
        link.href = base + path;
        link.dispatchEvent(new PointerEvent('pointerenter'));
    };
    try
    {
        enter('prefetch-dynamic');
        await wait();
        check(requests === 1, 'Dynamic link not prefetched exactly once');
        enter('prefetch-leave');
        link.dispatchEvent(new PointerEvent('pointerleave'));
        await wait();
        check(requests === 1, 'Leaving link did not cancel timer');
        enter('prefetch-navigation');
        document.dispatchEvent(new CustomEvent(NAVIGATION_START));
        await wait();
        check(requests === 1, 'Navigation did not cancel hover');
        enter('prefetch-detached');
        link.remove();
        await wait();
        check(requests === 1, 'Detached link prefetched');
        document.body.append(link);
        enter('deconnexion');
        await wait();
        check(requests === 1, 'Logout link prefetched');
        return ['dynamic links and single initialization', 'hover exit cancels pending prefetch', 'navigation cancels pending prefetch', 'detached and logout links ignored'];
    }
    finally
    {
        link.remove();
        window.fetch = originalFetch;
    }
}
