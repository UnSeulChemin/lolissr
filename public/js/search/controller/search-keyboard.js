// =========================================
// SEARCH KEYBOARD
// =========================================

import {
    $$,
} from '../../core/dom.js';

// =========================================
// UPDATE ACTIVE RESULT
// =========================================

export function updateActiveResult(
    searchResults,
    activeIndex,
)
{
    const items =
        $$(
            '.search-result-item',
            searchResults,
        );

    items.forEach(
        (item) =>
        {
            item.classList.remove(
                'is-active',
            );
        },
    );

    const activeItem =
        items[activeIndex];

    if (! activeItem)
    {
        return;
    }

    activeItem.classList.add(
        'is-active',
    );

    activeItem.scrollIntoView({
        block: 'nearest',
    });
}