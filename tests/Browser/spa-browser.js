export async function runBrowserScenario()
{
    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const {invalidatePage} = await import('./js/router/pages/invalidation.js');
    const {setPrefetchedPage, getPrefetchedPage} = await import('./js/router/prefetch/prefetch-cache.js');
    const {shouldRefreshRoute, clearInvalidatedRoute} = await import('./js/router/pages/route-invalidation.js');
    const {invalidateMangaPages} = await import('./js/manga/cache-invalidation.js');
    const {inFlight} = await import('./js/router/prefetch/prefetch-state.js');
    const {replaceContent} = await import('./js/router/navigation/page-dom.js');
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const results = [];
    const page = {type: 'page', page: {html: '<p>Cached</p>', format: 'fragment', stylesheets: []}};

    for (const prefix of ['/', '/lolissr/'])
    {
        for (const path of ['', '?page=2', 'manga', 'chinois']) setPrefetchedPage(prefix + path, page);
        invalidatePage(prefix, {descendants: false});
        check(!getPrefetchedPage(prefix) && !getPrefetchedPage(prefix + '?page=2'), 'Home variants must expire');
        check(getPrefetchedPage(prefix + 'manga') && getPrefetchedPage(prefix + 'chinois'), 'Exact invalidation evicted another section');
        check(shouldRefreshRoute(prefix) && !shouldRefreshRoute(prefix + 'chinois'), 'Exact route invalidation widened');
        clearInvalidatedRoute(prefix);
    }
    for (const path of ['manga/series/a', 'manga/series/b', 'profil/xp', 'chinois', 'figurine']) setPrefetchedPage(base + path, page);
    const pending = new AbortController();
    const unrelated = new AbortController();
    inFlight.set(new URL(base + 'manga/series/pending', location.origin).href, {controller: pending});
    inFlight.set(new URL(base + 'chinois/pending', location.origin).href, {controller: unrelated});
    invalidateMangaPages();
    check(!getPrefetchedPage(base + 'manga/series/a') && !getPrefetchedPage(base + 'manga/series/b'), 'Manga descendants survived');
    check(!getPrefetchedPage(base + 'profil/xp'), 'Profile XP cache survived');
    check(getPrefetchedPage(base + 'chinois') && getPrefetchedPage(base + 'figurine'), 'Unrelated section evicted');
    check(pending.signal.aborted && !unrelated.signal.aborted, 'Wrong prefetch request aborted');
    results.push('Exact home invalidation, section invalidation, query variants and selective request cancellation');

    const main = document.createElement('main');
    main.className = 'app-content';
    document.body.append(main);
    document.body.dataset.appInitialized = 'true';
    document.body.dataset.stale = 'yes';
    replaceContent('<h1 id="fragment-test">Fragment</h1>', {format: 'fragment', title: 'Fragment title', lang: 'fr', bodyData: {page: 'profile'}});
    check(main.querySelector('#fragment-test') && document.title === 'Fragment title', 'Fragment rendering failed');
    check(document.body.dataset.page === 'profile' && !document.body.dataset.stale && document.body.dataset.appInitialized === 'true', 'Body metadata changed incorrectly');
    replaceContent('<html lang="en"><head><title>Legacy</title></head><body data-page="legacy"><main class="app-content"><p id="legacy-test">Legacy</p></main></body></html>');
    check(main.querySelector('#legacy-test') && document.title === 'Legacy' && document.documentElement.lang === 'en', 'Legacy document rendering failed');
    results.push('Fragment and legacy document rendering, title, language and body metadata');

    const search = document.createElement('form');
    search.className = 'js-header-search';
    search.dataset.basePath = base;
    search.innerHTML = '<input id="header-search-input"><div class="js-header-search-dropdown"><div id="header-search-results"></div></div>';
    document.body.append(search);
    const originalFetch = window.fetch;
    let searchRequests = 0;
    let navigationRequests = 0;
    window.fetch = async (url, options) =>
    {
        const isSearch = String(url).includes('recherche?q=');
        if (isSearch) searchRequests++; else navigationRequests++;
        if (!isSearch) check(options.headers['X-Page-Format'] === 'fragment', 'Navigation did not request a fragment');
        return new Response(JSON.stringify(isSearch
            ? {success: true, data: {mangas: [{slug: 'spa-test', numero: 1, livre: 'SPA test'}]}}
            : {success: true, type: 'page', page: {html: '<p id="keyboard-test">Loaded</p>', format: 'fragment', title: 'Keyboard', lang: 'fr', bodyData: {}, stylesheets: []}}),
            {headers: {'Content-Type': 'application/json'}});
    };
    try
    {
        const {initSearchController} = await import('./js/search/controller/search-controller.js');
        initSearchController();
        const input = search.querySelector('input');
        input.value = 'SPA';
        search.dispatchEvent(new Event('submit', {cancelable: true}));
        const until = async (predicate) =>
        {
            for (let i = 0; i < 100; i++)
            { if (predicate()) return; await new Promise(resolve => setTimeout(resolve, 10)); }
            throw new Error('SPA test timed out');
        };
        await until(() => search.querySelector('.search-result-item'));
        input.dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowDown', cancelable: true}));
        input.dispatchEvent(new KeyboardEvent('keydown', {key: 'Enter', cancelable: true}));
        await until(() => main.querySelector('#keyboard-test'));
        check(searchRequests === 1 && navigationRequests === 1, 'Unexpected request count');
        results.push('One global search request; Enter navigates through the SPA without reloading');
    }
    finally
    { window.fetch = originalFetch; }
    return results;
}
