// =========================================
// APP CONFIG
// =========================================

const hostname =
    window.location.hostname;

const pathname =
    window.location.pathname;

const searchParams =
    new URLSearchParams(
        window.location.search,
    );

// =========================================
// ENV
// =========================================

const isLocalhost =
    hostname === 'localhost'
    || hostname === '127.0.0.1';

// =========================================
// DEBUG
// =========================================

const debugEnabled =
    searchParams.get(
        'debug',
    ) === '1';

// =========================================
// BASE URL
// =========================================

const baseUrl =
    pathname.startsWith(
        '/lolissr/',
    )
        ? '/lolissr/'
        : '/';

// =========================================
// ENVIRONMENT
// =========================================

const env =
    debugEnabled
        ? 'development'
        : 'production';

// =========================================
// CONFIG
// =========================================

export const config =
    Object.freeze({

        // =================================
        // APP
        // =================================

        env,

        debug:
            debugEnabled,

        isLocalhost,

        baseUrl,

        // =================================
        // ROUTER
        // =================================

        router:
        {
            timeout:
                10000,

            maxConcurrentNavigations:
                1,
        },

        // =================================
        // PREFETCH
        // =================================

        prefetch:
        {
            enabled:
                true,

            hoverDelay:
                80,

            timeout:
                8000,

            cacheLimit:
                50,

            cacheDuration:
                60000,
        },

        // =================================
        // TRANSITIONS
        // =================================

        transitions:
        {
            enabled:
                true,

            duration:
                250,
        },

        // =================================
        // NAVIGATION
        // =================================

        navigation:
        {
            backLockDuration:
                350,

            initialPrefetchDelay:
                800,

            restoreScroll:
                true,
        },

        // =================================
        // DEBUG PANEL
        // =================================

        debugPanel:
        {
            enabled:
                debugEnabled,

            maxLogs:
                30,
        },
    });