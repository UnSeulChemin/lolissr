// =========================================
// SEARCH DROPDOWN
// =========================================

// =========================================
// OPEN SEARCH DROPDOWN
// =========================================

export function openSearchDropdown(
    dropdown,
)
{
    if (
        !dropdown
    ) {
        return;
    }

    dropdown.classList.add(
        'is-active',
    );
}

// =========================================
// CLOSE SEARCH DROPDOWN
// =========================================

export function closeSearchDropdown(
    dropdown,
)
{
    if (
        !dropdown
    ) {
        return;
    }

    dropdown.classList.remove(
        'is-active',
    );
}

// =========================================
// CLEAR SEARCH RESULTS
// =========================================

export function clearSearchResults(
    container,
)
{
    if (
        !container
    ) {
        return;
    }

    container.innerHTML =
        '';
}