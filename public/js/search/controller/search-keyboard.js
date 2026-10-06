// =================================================
// NAVIGATION AU CLAVIER DE LA RECHERCHE
// =================================================

import { $$ } from '../../core/dom.js';

// =================================================
// MISE À JOUR ACTIF RÉSULTAT
// =================================================

export function updateActiveResult(searchResults, activeIndex)
{
    const items = $$('.search-result-item', searchResults);

    const activeItem = items[activeIndex];
    const previousItem = searchResults.querySelector('.search-result-item.is-active');
    if (previousItem === activeItem) return;
    previousItem?.classList.remove('is-active');

    if (! activeItem)
    {
        return;
    }

    activeItem.classList.add('is-active');

    activeItem.scrollIntoView({
        block: 'nearest'
    });
}
