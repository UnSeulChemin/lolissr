export async function runBrowserScenario()
{
    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const {initHideRecommendation} = await import('./js/manga/actions/hide-recommendation.js');
    const {runCleanup} = await import('./js/router/lifecycle/cleanup.js');
    const originalFetch = window.fetch;
    const main = document.createElement('main');
    main.className = 'app-content';
    document.body.append(main);
    const until = async predicate =>
    {
        for (let i = 0; i < 100; i++)
        { if (predicate()) return; await new Promise(resolve => setTimeout(resolve, 10)); }
        throw new Error('Recommendation pagination refresh timed out');
    };
    try
    {
        for (const [route, action, empty] of [
            ['recommandations/categorie/romance', 'hide', false],
            ['recommandations-masquees', 'restore', false],
            ['favoris', 'favorite', true]
        ])
        {
            runCleanup();
            const canonical = base + 'manga/series/' + route;
            history.replaceState({}, '', canonical + '/page/2');
            main.innerHTML = `<section data-recommendation-url="${location.pathname}"><div class="collection-grid"><div class="collection-release-item"><form class="js-${action}-recommendation" data-favorites-page="true" action="${canonical}/retirer"><button type="submit">Modifier</button></form></div></div></section>`;
            let posts = 0;
            let reads = 0;
            window.fetch = async (url, options) =>
            {
                if (options?.method === 'POST')
                {
                    posts++;
                    return new Response(JSON.stringify({success: true, message: 'Updated'}), {headers: {'Content-Type': 'application/json'}});
                }
                reads++;
                if (!String(url).includes('reconcile=1')) throw new Error('Refresh omitted pagination reconciliation');
                return new Response(JSON.stringify({success: true, type: 'page', page: {
                    html: `<section data-recommendation-url="${canonical}">${empty ? '' : '<nav class="collection-pagination-wrapper">1</nav><div class="collection-release-item">Replacement</div>'}</section>`,
                    format: 'fragment', title: 'Recommendations', stylesheets: [], lang: 'fr', bodyData: {}
                }}), {headers: {'Content-Type': 'application/json'}});
            };
            initHideRecommendation();
            main.querySelector('form').dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
            if (action === 'hide') document.querySelector('.confirm-modal-primary').click();
            await until(() => location.pathname === canonical && !main.querySelector('form'));
            if (posts !== 1 || reads !== 1 || location.search !== '') throw new Error('Mutation failed to refresh and canonicalize the current page');
            if (empty && main.querySelector('[data-recommendation-url]').children.length !== 0) throw new Error('Empty favorites retained content or navigation');
        }
        return ['hide refills filtered list', 'restore reconciles last page', 'last favorite leaves an empty page', 'canonical URL without refresh parameter'];
    }
    finally
    {
        runCleanup();
        window.fetch = originalFetch;
        main.remove();
    }
}
