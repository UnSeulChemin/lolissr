// ==================================================
// GLOBAL INITIALIZERS
// ==================================================

import {
    initRouter,
} from '../router/router.js';

import {
    initPrefetch,
} from '../router/prefetch.js';

import {
    initNavigationLoading,
} from '../router/navigation-loading.js';

import {
    initRouterDebugPanel,
} from '../router/router-debug-panel.js';

import {
    initGlobalBackNavigation,
} from '../history/back-navigation.js';

import {
    initGlobalErrorHandlers,
} from '../boot/app-errors.js';

// ==================================================
// EXPORT
// ==================================================

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