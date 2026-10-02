export async function testPageStyles()
{
    const {createFlashcardDeck} = await import('./js/chinois/pages/flashcard-deck.js');
    const {runCleanup} = await import('./js/router/router-cleanup.js');
    const check = (ok, message) => { if (!ok) throw new Error(message); };
    const original = window.fetch;
    let rows = Array.from({length: 121}, (_, i) => ({id: (i + 1) * 2}));
    let requests = 0;
    const container = document.createElement('div');
    container.dataset.flashcards = JSON.stringify(rows.slice(0, 50));
    container.dataset.flashcardTotal = String(rows.length);
    const serve = async url => {
        requests++;
        const requested = Number(String(url).split('/').at(-1));
        const offset = Math.floor(Math.min(requested, Math.max(0, rows.length - 1)) / 50) * 50;
        return new Response(JSON.stringify({success: true, data: {
            cards: rows.slice(offset, offset + 50), total: rows.length, offset,
        }}), {headers: {'Content-Type': 'application/json'}});
    };
    try
    {
        window.fetch = serve;
        const deck = createFlashcardDeck(container, 'vocabulaire');
        for (let i = 0; i < 49; i++) await deck.move(1);
        check(requests === 0 && deck.card.id === 100, 'Initial batch requested again');
        await deck.move(1);
        check(requests === 1 && deck.card.id === 102 && deck.index === 50, 'Next batch failed');
        await deck.move(-1);
        check(deck.card.id === 100 && deck.index === 49, 'Previous batch failed');
        for (let i = 0; i < 49; i++) await deck.move(-1);
        await deck.move(-1);
        check(deck.card.id === 242 && deck.index === 120, 'First-to-last wrap failed');
        await deck.move(1);
        check(deck.card.id === 2 && deck.index === 0, 'Last-to-first wrap failed');

        rows.shift();
        await deck.remove(2);
        check(deck.total === 120 && deck.card.id === 4 && deck.index === 0, 'Removal did not refresh offsets/count');
        window.fetch = async () => { throw new TypeError('Simulated network failure'); };
        let failed = false;
        try { await deck.move(-1); } catch { failed = true; }
        check(failed && deck.index === 0 && deck.card.id === 4, 'Network error changed the current card');
        window.fetch = serve;
        await deck.move(-1);
        check(deck.card.id === 242, 'Retry failed');
        rows = rows.slice(0, 1);
        await deck.remove(242);
        check(deck.total === 1 && deck.card.id === 4, 'Concurrent shrink failed');
        rows = [];
        await deck.remove(4);
        check(deck.total === 0 && !deck.card, 'Final removal failed');

        const cancelled = createFlashcardDeck(container, 'grammaire');
        let release;
        window.fetch = url => new Promise(resolve => { release = async () => resolve(await serve(url)); });
        const pending = cancelled.move(-1);
        runCleanup();
        await release();
        failed = false;
        try { await pending; } catch { failed = true; }
        check(failed && cancelled.card.id === 2 && cancelled.total === 121, 'Late response survived cleanup');
        return ['Bounded batches and sparse IDs', 'Forward/backward boundaries and circular navigation',
            'Removal, concurrent shrink and empty deck', 'Network failure preserves current card and retry works',
            'Cleanup discards late responses'];
    }
    finally { window.fetch = original; runCleanup(); }
}
