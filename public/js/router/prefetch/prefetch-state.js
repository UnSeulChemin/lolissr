// =========================================
// PREFETCH STATE
// =========================================

export const PREFETCH_STATE =
    window.__PREFETCH_STATE__
    ||= {

        initialized:
            false,

        cache:
            new Map(),

        inFlight:
            new Map(),

        invalidated:
            new Set(),
    };

// =========================================
// STATE
// =========================================

export const cache =
    PREFETCH_STATE.cache;

export const inFlight =
    PREFETCH_STATE.inFlight;

export const invalidated =
    PREFETCH_STATE.invalidated;