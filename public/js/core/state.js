// ==================================================
// App State
// ==================================================

export const state =
{
    // ==============================================
    // Navigation
    // ==============================================

    navigating:
        false,

    currentUrl:
        window.location.href,

    // ==============================================
    // Cache
    // ==============================================

    prefetchedPages:
        new Map(),

    pendingRequests:
        new Map(),

    recentPrefetches:
        new Map(),

    // ==============================================
    // Debug
    // ==============================================

    debug:
        window.location.hostname
            === 'localhost',
};