// =========================================
// APP STATE
// =========================================

export const state =
{
    // =====================================
    // NAVIGATION
    // =====================================

    navigating:
        false,

    currentUrl:
        window.location.href,

    previousUrl:
        null,

    // =====================================
    // PREFETCH
    // =====================================

    prefetching:
        false,
};

// =========================================
// HELPERS
// =========================================

export function setNavigating(
    value,
)
{
    state.navigating =
        Boolean(
            value,
        );
}

export function setCurrentUrl(
    url,
)
{
    state.previousUrl =
        state.currentUrl;

    state.currentUrl =
        url;
}

export function setPrefetching(
    value,
)
{
    state.prefetching =
        Boolean(
            value,
        );
}