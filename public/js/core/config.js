// =========================================
// APP CONFIG
// =========================================

const hostname =
    window.location.hostname;

const pathname =
    window.location.pathname;

// =========================================
// ENV
// =========================================

const isLocalhost =
    hostname === 'localhost'
    || hostname === '127.0.0.1';

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
// CONFIG
// =========================================

export const config =
    Object.freeze({

        // =================================
        // APP
        // =================================

        debug:
            isLocalhost,

        baseUrl,

        // =================================
        // AJAX
        // =================================

        ajax:
        {
            timeout:
                10000,
        },

        // =================================
        // PREFETCH
        // =================================

        prefetch:
        {
            delay:
                100,

            timeout:
                5000,

            cacheDuration:
                10000,
        },

        // =================================
        // TRANSITIONS
        // =================================

        transitions:
        {
            duration:
                250,
        },

        // =================================
        // NAVIGATION
        // =================================

        navigation:
        {
            backLockDuration:
                120,
        },
    });