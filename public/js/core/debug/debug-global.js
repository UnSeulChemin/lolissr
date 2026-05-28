// =========================================
// DEBUG GLOBAL
// =========================================

import {
    enableDebug,
    disableDebug,
} from './debug-storage.js';

import {
    getLogs,
    clearLogs,
} from './logger.js';

// =========================================
// GLOBALS
// =========================================

window.enableDebug =
    enableDebug;

window.disableDebug =
    disableDebug;

window.__LOGS__ =
    getLogs;

window.__CLEAR_LOGS__ =
    clearLogs;