import { get } from '../../core/http.js';
import { registerCleanup } from '../../router/router-cleanup.js';

// Keep only two batches of card contents; IDs preserve the full navigation order.
export function createFlashcardDeck(container, type)
{
    const controller = new AbortController();
    registerCleanup(() => controller.abort());
    const initial = JSON.parse(container.dataset.flashcards ?? '[]');
    const ids = JSON.parse(container.dataset.flashcardIds ?? '[]');
    const cache = new Map(initial.map(card => [card.id, card]));
    const firstIndex = ids.indexOf(initial[0]?.id);
    const baseUri = container.dataset.baseUri ?? '/';
    let index = Math.max(0, firstIndex);

    async function load()
    {
        while (ids.length > 0)
        {
            if (controller.signal.aborted)
            {
                throw new DOMException('Flashcards closed', 'AbortError');
            }
            const id = ids[index];
            if (cache.has(id)) return;

            const response = await get(`${baseUri}chinois/flashcards/${type}/cards/${id}`, { signal: controller.signal });
            if (controller.signal.aborted)
            {
                throw new DOMException('Flashcards closed', 'AbortError');
            }
            if (! response?.success || ! Array.isArray(response.data?.cards))
            {
                throw new Error('Chargement des cartes impossible');
            }

            for (const card of response.data.cards)
            {
                cache.delete(card.id);
                cache.set(card.id, card);
            }
            while (cache.size > 100)
            {
                cache.delete(cache.keys().next().value);
            }
            if (cache.has(id)) return;

            // The endpoint returns up to 50 cards ordered by id, starting at id.
            // Missing IDs through its last result are obsolete; an empty result
            // means the entire remaining suffix has disappeared.
            const returned = new Set(response.data.cards.map(card => card.id));
            const last = response.data.cards.at(-1)?.id ?? Infinity;
            for (let position = ids.length - 1; position >= 0; position--)
            {
                if (ids[position] >= id && ids[position] <= last && !returned.has(ids[position]))
                {
                    cache.delete(ids[position]);
                    ids.splice(position, 1);
                }
            }
            index = Math.max(0, ids.findIndex(candidate => candidate >= id));
        }
    }

    return {
        get card() { return cache.get(ids[index]); },
        get index() { return index; },
        get total() { return ids.length; },
        async move(direction)
        {
            if (! ids.length) return;
            const previousId = ids[index];
            index = (index + direction + ids.length) % ids.length;
            try
            {
                await load();
            }
            catch (error)
            {
                index = Math.max(0, ids.indexOf(previousId));
                throw error;
            }
        },
        async remove(id)
        {
            const position = ids.indexOf(id);
            if (position !== -1) ids.splice(position, 1);
            cache.delete(id);
            index %= ids.length || 1;
            await load();
        },
    };
}
