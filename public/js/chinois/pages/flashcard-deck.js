import { get } from '../../core/http.js';
import { registerCleanup } from '../../router/router-cleanup.js';

// Conserver un seul lot ; le serveur fournit le total actuel à chaque chargement.
export function createFlashcardDeck(container, type)
{
    const controller = new AbortController();
    registerCleanup(() => controller.abort());
    let cards = JSON.parse(container.dataset.flashcards ?? '[]');
    let total = Number(container.dataset.flashcardTotal ?? cards.length);
    let offset = 0;
    let index = 0;
    const baseUri = container.dataset.baseUri ?? '/';

    async function load(id, previous = false)
    {
        if (controller.signal.aborted)
        {
            throw new DOMException('Flashcards fermées', 'AbortError');
        }
        const response = await get(`${baseUri}chinois/flashcards/${type}/cursor/${previous ? 'previous' : 'next'}/${id}`, { signal: controller.signal });
        if (controller.signal.aborted)
        {
            throw new DOMException('Flashcards fermées', 'AbortError');
        }
        const page = response?.data;
        if (! response?.success || ! Array.isArray(page?.cards)
            || ! Number.isInteger(page.total) || page.total < 0
            || ! Number.isInteger(page.offset) || page.offset < 0
            || page.cards.length > 50 || page.offset + page.cards.length > page.total)
        {
            throw new Error('Chargement des cartes impossible');
        }

        // Valider la navigation après le succès de la requête et conserver la carte en cas d’erreur.
        cards = page.cards;
        total = page.total;
        offset = page.offset;
        index = cards.length ? offset + (previous ? cards.length - 1 : 0) : 0;
        if (! cards.length) total = 0;
    }

    return {
        get card()
        { return cards[index - offset]; },
        get index()
        { return index; },
        get total()
        { return total; },
        async move(direction)
        {
            if (! total) return;
            const target = index + direction;
            if (target >= offset && target < offset + cards.length)
            {
                index = target;
                return;
            }
            await load(cards[index - offset].id, direction < 0);
        },
        async remove(id)
        {
            const position = cards.findIndex(card => card.id === id);
            if (position === -1) return;
            // Reprendre après l’identifiant supprimé, même si d’autres cartes ont disparu simultanément.
            await load(id);
        }
    };
}
