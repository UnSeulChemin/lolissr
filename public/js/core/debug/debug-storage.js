// =================================================
// DÉBOGAGE STOCKAGE
// =================================================

const DEBUG_KEY = 'lolissr_debug';

// =================================================
// ACTIVATION
// =================================================

export function enableDebug()
{
    localStorage.setItem(DEBUG_KEY, '1');
}

// =================================================
// DÉSACTIVATION
// =================================================

export function disableDebug()
{
    localStorage.removeItem(DEBUG_KEY);
}

// =================================================
// STATUT
// =================================================

export function isDebugEnabled()
{
    return (localStorage.getItem( DEBUG_KEY ) === '1');
}