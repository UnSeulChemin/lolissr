import { get } from '../../core/http.js';
import { registerCleanup } from '../../router/router-cleanup.js';

// Only one batch is retained; the server supplies the current total on each fetch.
export function createFlashcardDeck(container, type)
{
    const controller = new AbortController();
    registerCleanup(() => controller.abort());
    let cards = JSON.parse(container.dataset.flashcards ?? '[]');
    let total = Number(container.dataset.flashcardTotal ?? cards.length);
    let offset = 0;
    let index = 0;
    const baseUri = container.dataset.baseUri ?? '/';

    async function load(target, refresh = false)
    {
        if (controller.signal.aborted)
        {
            throw new DOMException('Flashcards closed', 'AbortError');
        }
        if (! refresh && target >= offset && target < offset + cards.length)
        {
            index = target;
            return;
        }

        const pageOffset = Math.floor(target / 50) * 50;
        const response = await get(`${baseUri}chinois/flashcards/${type}/batch/${pageOffset}`, { signal: controller.signal });
        if (controller.signal.aborted)
        {
            throw new DOMException('Flashcards closed', 'AbortError');
        }
        const page = response?.data;
        if (! response?.success || ! Array.isArray(page?.cards)
            || ! Number.isInteger(page.total) || page.total < 0
            || ! Number.isInteger(page.offset) || page.offset < 0)
        {
            throw new Error('Chargement des cartes impossible');
        }

        // Commit navigation only once the request succeeds, preserving the card on errors.
        cards = page.cards;
        total = page.total;
        offset = page.offset;
        index = cards.length ? Math.max(offset, Math.min(target, offset + cards.length - 1)) : 0;
        if (! cards.length) total = 0;
    }

    return {
        get card() { return cards[index - offset]; },
        get index() { return index; },
        get total() { return total; },
        async move(direction)
        {
            if (! total) return;
            await load((index + direction + total) % total);
        },
        async remove(id)
        {
            const position = cards.findIndex(card => card.id === id);
            if (position === -1) return;
            // Reload after a successful mutation: offsets and the total may have changed.
            await load(index % Math.max(1, total - 1), true);
        },
    };
}
