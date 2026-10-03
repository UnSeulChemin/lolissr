export async function runBrowserScenario()
{
    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const until = async predicate =>
    {
        for (let i = 0; i < 100; i++)
        { if (predicate()) return; await new Promise(resolve => setTimeout(resolve, 10)); }
        throw new Error('Lifecycle test timed out');
    };
    const main = document.createElement('main');
    main.className = 'app-content';
    document.body.append(main);
    const {ROUTE_INITIALIZERS} = await import('./js/router/initializers/route-initializers.js');
    const {GLOBAL_INITIALIZERS} = await import('./js/boot/global-initializers.js');
    const {initApp} = await import('./js/boot/app-init.js');
    const {navigateTo} = await import('./js/router/router-navigation.js');
    const originalFetch = window.fetch;
    window.fetch = async () => new Response(JSON.stringify({success: true, type: 'page', page: {
        html: '<p id="arrived">Destination</p>', format: 'fragment', title: 'Destination', stylesheets: [], lang: 'fr', bodyData: {}
    }}), {headers: {'Content-Type': 'application/json'}});
    try
    {
        let release;
        let started = false;
        let initialized = 0;
        let staleInitialized = false;
        const gate = new Promise(resolve =>
        { release = resolve; });
        const slow = () =>
        { staleInitialized = true; };
        slow.preload = () =>
        { started = true; return gate; };
        ROUTE_INITIALIZERS.splice(0, ROUTE_INITIALIZERS.length,
            {match: /^\/manga$/, initializers: [['Slow first route', slow]]},
            {match: /^\/figurine$/, initializers: [['Destination', () =>
            { initialized++; }]]});
        history.replaceState({}, '', base + 'manga');
        const boot = initApp();
        await until(() => started);
        await navigateTo(base + 'figurine');
        release();
        await boot;
        check(main.querySelector('#arrived') && initialized === 1 && !staleInitialized,
            'Navigation during initial imports missed its initializer or initialized the stale route');

        // The router can also navigate before global initialization finishes.
        let releaseGlobal;
        let globalStarted = false;
        GLOBAL_INITIALIZERS.push(['Slow global', () => new Promise(resolve =>
        { globalStarted = true; releaseGlobal = resolve; })]);
        history.replaceState({}, '', base + 'manga');
        const secondBoot = initApp();
        await until(() => globalStarted);
        await navigateTo(base + 'figurine');
        releaseGlobal();
        await secondBoot;
        check(initialized === 2, 'Navigation during global boot initialized the destination more than once');
    }
    finally
    { window.fetch = originalFetch; }

    const {setPrefetchedPage, getPrefetchedPage, invalidatePrefetch} = await import('./js/router/prefetch/prefetch-cache.js');
    const {renderPage} = await import('./js/router/navigation/navigation-render.js');
    const originalNow = Date.now;
    let clock = 100000;
    Date.now = () => clock;
    try
    {
        const target = base + 'cache-lifecycle-fixture';
        const response = {type: 'page', page: {html: '<p>Snapshot</p>', format: 'fragment', title: 'Snapshot', bodyData: {}, stylesheets: []}};
        setPrefetchedPage(target, response);
        clock += 59000;
        const cached = getPrefetchedPage(target);
        check(cached, 'Snapshot expired early');
        await renderPage(location.href, target, cached, {updateHistory: false});
        clock += 1000;
        check(getPrefetchedPage(target) === null, 'Cached rendering renewed snapshot age');
        setPrefetchedPage(target, cached);
        check(getPrefetchedPage(target) === null, 'Evicted stale response was resurrected');
        const fresh = {type: 'page', page: {...response.page, html: '<p>Fresh</p>'}};
        setPrefetchedPage(target, fresh);
        check(getPrefetchedPage(target)?.page.html === '<p>Fresh</p>', 'Fresh response was not cached');
        invalidatePrefetch(target);
        check(getPrefetchedPage(target) === null, 'Explicit invalidation failed');
    }
    finally
    { Date.now = originalNow; }
    return ['navigation during initial imports', 'navigation during global boot without duplicate initialization',
        'fixed snapshot expiration including eviction', 'fresh responses and explicit invalidation'];
}
