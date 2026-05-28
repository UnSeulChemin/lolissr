// =========================================
// PREFETCH
// =========================================

export {
    initPrefetch,
} from './prefetch/prefetch-init.js';

export {
    prefetchPage,
} from './prefetch/prefetch-request.js';

export {
    getPrefetchedPage,
    getInFlightPrefetch,
    invalidatePrefetch,
    clearPrefetchCache,
} from './prefetch/prefetch-cache.js';