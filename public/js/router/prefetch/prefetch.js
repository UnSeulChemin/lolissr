// =========================================
// PREFETCH
// =========================================

export {
    initPrefetch,
} from './prefetch-init.js';

export {
    prefetchPage,
    getInFlightPrefetch,
} from './prefetch-request.js';

export {
    getPrefetchedPage,
    invalidatePrefetch,
    clearPrefetchCache,
} from './prefetch-cache.js';