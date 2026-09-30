export async function testPageStyles()
{
    const base = new URL('.', location.href).pathname;
    window.appConfig = {baseUri: base};
    const {createFlashcardDeck} = await import('./js/chinois/pages/flashcard-deck.js');
    const {resolvePage} = await import('./js/router/navigation/resolve-page.js');
    const {renderPage} = await import('./js/router/navigation/navigation-render.js');
    const {setPrefetchedPage} = await import('./js/router/prefetch/prefetch-cache.js');
    const check = (ok, text) => { if (!ok) throw new Error(text); };
    const original = window.fetch;
    let requests = 0;
    try
    {
        const container = document.createElement('div');
        container.dataset.baseUri = base;
        container.dataset.flashcardIds = JSON.stringify(Array.from({length: 500}, (_, i) => i + 1));
        container.dataset.flashcards = JSON.stringify([{id: 1}]);
        window.fetch = async () => {
            requests++;
            return new Response(JSON.stringify({success: true, data: {cards: []}}), {headers: {'Content-Type': 'application/json'}});
        };
        const deck = createFlashcardDeck(container, 'vocabulaire');
        await deck.move(1);
        check(requests === 1 && deck.total === 1 && deck.card.id === 1, 'Empty batch caused repeated requests for removed cards');
        requests = 0;
        const partial = createFlashcardDeck(container, 'grammaire');
        window.fetch = async () => {
            requests++;
            return new Response(JSON.stringify({success: true, data: {cards: [{id: 400}, {id: 401}]}}), {headers: {'Content-Type': 'application/json'}});
        };
        await partial.move(1);
        check(requests === 1 && partial.card.id === 400 && partial.total === 102, 'Gaps not reconciled from ordered batch');

        const main = document.createElement('main');
        main.className = 'app-content';
        document.body.append(main);
        const toast = document.createElement('div');
        toast.id = 'toast';
        document.body.append(toast);
        const page = {html: '<p>Feedback</p>', format: 'fragment', title: 'Feedback', stylesheets: [], bodyData: {}};
        setPrefetchedPage(base + 'manga', {type: 'page', page: {...page, requiresFreshNavigation: true}});
        requests = 0;
        window.fetch = async () => {
            requests++;
            return new Response(JSON.stringify({type: 'page', page: {...page, flashToast: {message: 'Saved once', type: 'success'}}}), {headers: {'Content-Type': 'application/json'}});
        };
        const response = await resolvePage(base + 'manga', false, new AbortController().signal);
        check(requests === 1, 'Pending feedback used speculative cached response');
        await renderPage(location.href, base + 'manga', response, {});
        check(toast.textContent.includes('Saved once'), 'Navigation did not display feedback');
        toast.textContent = 'unchanged';
        await renderPage(location.href, base + 'manga', response, {});
        check(toast.textContent === 'unchanged', 'Cached navigation replayed feedback');
        return ['Empty batches remove obsolete suffix in one request', 'Partial batches reconcile missing IDs', 'Pending feedback bypasses prefetch', 'SPA feedback displays once'];
    }
    finally { window.fetch = original; }
}
