// =========================================
// APP CONFIG
// =========================================

const isLocalhost =
    window.location.hostname
        === 'localhost';

const isSubdirectoryInstall =
    window.location.pathname
        .startsWith(
            '/lolissr/',
        );

const baseUrl =
    isSubdirectoryInstall
        ? '/lolissr/'
        : '/';

// =========================================
// Config
// =========================================

export const config =
{
    // =====================================
    // App
    // =====================================

    debug:
        isLocalhost,

    baseUrl,

    // =====================================
    // AJAX
    // =====================================

    ajax:
    {
        timeout:
            10000,
    },

    // =====================================
    // Prefetch
    // =====================================

    prefetch:
    {
        delay:
            80,

        timeout:
            8000,

        cooldown:
            3000,

        cacheLimit:
            50,
    },

    // =====================================
    // Transitions
    // =====================================

    transitions:
    {
        duration:
            250,
    },

    // =====================================
    // Navigation
    // =====================================

    navigation:
    {
        backLockDuration:
            350,

        initialPrefetchDelay:
            800,
    },
};