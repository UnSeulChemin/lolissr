// =================================================
// GLOBAL INITIALISATIONS
// =================================================

import {
    initGlobalErrorHandlers,
} from './app-errors.js';

import {
    initCopy,
} from '../core/copy/copy-init.js';

import {
    initGlobalBackNavigation,
} from '../router/history/back-navigation.js';

import {
    initPrefetch,
} from '../router/prefetch/prefetch-init.js';

import {
    initRouter,
} from '../router/router.js';

import {
    initNavigationLoading,
} from '../router/ui/navigation-loading.js';

import {
    initRouterDebugPanel,
} from '../router/ui/router-debug-panel.js';

import {
    initSearchController,
} from '../search/controller/search-controller.js';

// =================================================
// EXPORT
// =================================================

export const GLOBAL_INITIALIZERS = [
    [
        'Router',
        initRouter,
    ],
    [
        'Prefetch',
        initPrefetch,
    ],
    [
        'Copy',
        initCopy,
    ],
    [
        'GlobalSearchController',
        initSearchController,
    ],
    [
        'NavigationLoading',
        initNavigationLoading,
    ],
    [
        'RouterDebugPanel',
        initRouterDebugPanel,
    ],
    [
        'GlobalBackNavigation',
        initGlobalBackNavigation,
    ],
    [
        'GlobalErrorHandlers',
        initGlobalErrorHandlers,
    ],
];