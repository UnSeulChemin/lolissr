// ==================================================
// App State
// ==================================================

export const state =
{
    navigating:
        false,

    currentUrl:
        window.location.href,

    cache:
        new Map(),

    prefetched:
        new Map(),

    debug:
        true,
};