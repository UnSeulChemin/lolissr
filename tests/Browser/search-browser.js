export async function runBrowserScenario()
{
    const {cachedSearch, invalidateSearchCache} = await import('./js/search/cache.js');
    const check = (ok, message) =>
    { if (!ok) throw new Error(message); };
    const {highlightSearchTerm} = await import('./js/search/utils/search-utils.js');
    const mark = value => `<mark class="search-highlight">${value}</mark>`;
    check(highlightSearchTerm('Test test', 'test') === `${mark('Test')} ${mark('test')}`, 'Repeated highlighting changed');
    check(highlightSearchTerm('TEST', 'test') === mark('TEST'), 'Reused pattern lost matches');
    check(highlightSearchTerm('<a+b>', 'a+b') === `&lt;${mark('a+b')}&gt;`, 'Literal query or escaping changed');
    check(highlightSearchTerm('<script>', '') === '&lt;script&gt;', 'Empty query bypassed escaping');
    check(highlightSearchTerm('one TWO', 'one two') === `${mark('one')} ${mark('TWO')}`, 'Multiword query changed');
    const {invalidateMangaPages} = await import('./js/manga/cache-invalidation.js');
    const {shouldRefreshRoute} = await import('./js/router/pages/route-invalidation.js');
    const {appUrl} = await import('./js/core/url.js');
    let invalidations = 0;
    const countInvalidation = () => invalidations++;
    document.addEventListener('search:invalidate', countInvalidation);
    invalidateMangaPages();
    document.removeEventListener('search:invalidate', countInvalidation);
    check(invalidations === 1, 'Batch reset search more than once');
    check(shouldRefreshRoute(appUrl('manga')) && shouldRefreshRoute(appUrl('profil'))
        && shouldRefreshRoute(appUrl()), 'Batch lost route invalidations');
    const originalNow = Date.now;
    let now = originalNow();
    Date.now = () => now;
    let loads = 0;
    const load = async () => ({value: ++loads});
    try
    {
        invalidateSearchCache();
        await cachedSearch('?q=one', null, load);
        await cachedSearch('?q=one', null, load);
        check(loads === 1, 'Repeated search missed cache');
        now += 30000;
        await cachedSearch('?q=one', null, load);
        check(loads === 2, 'Expired search reused');
        for (let i = 0; i < 20; i++) await cachedSearch('?q=' + i, null, load);
        await cachedSearch('?q=one', null, load);
        check(loads === 23, 'Search cache is unbounded');
        let finish;
        const pending = cachedSearch('?q=pending', null, () => new Promise(resolve =>
        {finish = resolve;}));
        invalidateSearchCache();
        finish({});
        await pending.then(() =>
        {throw new Error('Invalidated response accepted');}, error => check(error.name === 'AbortError', 'Wrong invalidation error'));
        const aborted = new AbortController();
        aborted.abort();
        await cachedSearch('?q=abort', aborted.signal, load).then(() =>
        {throw new Error('Aborted search accepted');}, error => check(error.name === 'AbortError', 'Wrong abort error'));
    }
    finally
    { Date.now = originalNow; }

    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const originalFetch = window.fetch;
    let requests = 0;
    window.fetch = async () =>
    {
        requests++;
        return new Response(JSON.stringify({success: true, data: {figurines: [{slug: 'test', numero: 1, waifu: 'Test', origin: 'Test'}]}}), {headers: {'Content-Type': 'application/json'}});
    };
    const form = document.createElement('form');
    form.className = 'js-header-search';
    form.dataset.basePath = base;
    form.innerHTML = '<input id="header-search-input"><div class="js-header-search-dropdown"><div id="header-search-results"></div></div>';
    document.body.append(form);
    try
    {
        const {initSearchController} = await import('./js/search/controller/search-controller.js');
        initSearchController();
        initSearchController();
        const input = form.querySelector('input');
        const until = async predicate =>
        {
            for (let i = 0; i < 200; i++)
            {
                if (predicate()) return;
                await new Promise(resolve => setTimeout(resolve, 10));
            }
            throw new Error('Search rendering timed out');
        };
        const search = async () =>
        {
            input.value = 'test';
            form.dispatchEvent(new Event('submit', {cancelable: true}));
            await until(() => form.querySelector('.search-result-item'));
        };
        await search();
        check(requests === 1, 'Duplicate initialization requested twice');
        form.querySelector('.search-result-item strong').dispatchEvent(new MouseEvent('mouseover', {bubbles: true}));
        check(form.querySelector('.search-result-item').classList.contains('is-active'), 'Delegated hover failed');
        input.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape', bubbles: true}));
        await search();
        check(requests === 1, 'Controller did not reuse cache');
        const {invalidatePage} = await import('./js/router/pages/invalidation.js');
        invalidatePage(base + 'figurine');
        check(!form.querySelector('.search-result-item'), 'Mutation left stale results');
        await search();
        check(requests === 2, 'Mutation reused stale search');
        input.dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowDown', bubbles: true, cancelable: true}));
        check(form.querySelector('.search-result-item').classList.contains('is-active'), 'Keyboard navigation failed');
        return ['search cache reuse, expiry and bound', 'invalidated and aborted responses rejected', 'lazy renderer and single initialization', 'delegated hover and keyboard navigation', 'mutation invalidates search data and displayed results'];
    }
    finally
    {
        invalidateSearchCache();
        form.remove();
        window.fetch = originalFetch;
    }
}
