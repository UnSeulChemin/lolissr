export async function runBrowserScenario()
{
    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const {initDeleteVocabulary} = await import('./js/chinois/actions/delete-vocabulary.js');
    const {runCleanup} = await import('./js/router/lifecycle/cleanup.js');
    const originalFetch = window.fetch;
    const main = document.createElement('main');
    main.className = 'app-content';
    document.body.append(main);
    const until = async predicate =>
    {
        for (let i = 0; i < 100; i++)
        { if (predicate()) return; await new Promise(resolve => setTimeout(resolve, 10)); }
        throw new Error('Vocabulary deletion refresh timed out');
    };
    try
    {
        for (const source of ['page/2', 'page/1', 'recherche/123'])
        {
            runCleanup();
            const canonical = base + 'chinois/vocabulaire/mandarin';
            history.replaceState({}, '', canonical + '/' + source);
            const target = source.startsWith('recherche') ? canonical : location.pathname;
            main.innerHTML = `<div data-vocabulary-url="${target}"><article class="chinois-vocab-card"><button class="vocabulaire-delete" data-id="123" data-url="${base}fixture-delete">Supprimer</button></article><nav class="collection-pagination-wrapper">2</nav></div>`;
            let posts = 0;
            let reads = 0;
            window.fetch = async (url, options) =>
            {
                if (options?.method === 'POST')
                {
                    posts++;
                    return new Response(JSON.stringify({success: true, message: 'Deleted'}), {headers: {'Content-Type': 'application/json'}});
                }
                reads++;
                if (!String(url).includes('reconcile=1')) throw new Error('Refresh omitted reconciliation');
                return new Response(JSON.stringify({success: true, type: 'page', page: {
                    html: `<div data-vocabulary-url="${canonical}"><p>Updated list</p></div>`,
                    format: 'fragment', title: 'Vocabulary', stylesheets: [], lang: 'fr', bodyData: {}
                }}), {headers: {'Content-Type': 'application/json'}});
            };
            initDeleteVocabulary();
            main.querySelector('button').click();
            document.querySelector('.confirm-modal-danger').click();
            await until(() => location.pathname === canonical && main.textContent.includes('Updated list') && location.search === '');
            if (posts !== 1 || reads !== 1 || main.querySelector('nav')) throw new Error('Stale pagination or duplicate request');
        }
        return ['last page reconciled', 'first page refreshed', 'detail deletion returns to list', 'canonical URL restored'];
    }
    finally
    {
        runCleanup();
        window.fetch = originalFetch;
        main.remove();
    }
}
