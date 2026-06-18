// =========================================
// SEARCH DROPDOWN
// =========================================

export function openSearchDropdown(
    dropdown,
)
{
    dropdown?.classList.add(
        'has-results',
    );
}

export function closeSearchDropdown(
    dropdown,
)
{
    dropdown?.classList.remove(
        'has-results',
    );
}

export function clearSearchResults(
    container,
)
{
    container?.replaceChildren();
}