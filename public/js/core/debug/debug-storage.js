// =========================================
// DEBUG STORAGE
// =========================================

const DEBUG_KEY =
    'lolissr_debug';

// =========================================
// ENABLE
// =========================================

export function enableDebug()
{
    localStorage.setItem(
        DEBUG_KEY,
        '1',
    );
}

// =========================================
// DISABLE
// =========================================

export function disableDebug()
{
    localStorage.removeItem(
        DEBUG_KEY,
    );
}

// =========================================
// STATUS
// =========================================

export function isDebugEnabled()
{
    return (
        localStorage.getItem(
            DEBUG_KEY,
        ) === '1'
    );
}