// =========================================
// APP STATE
// =========================================

export const state =
{
    navigating:
        false,

    currentUrl:
        window.location.href,

    previousUrl:
        null,
};

// =========================================
// NAVIGATION
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