import { config } from '../config.js';
let logger;
let failed = false;
const pending = [];
if (config.debug)
{
    void import('./logger.js').then(module =>
    {
        logger = module;
        for (const [method, args] of pending.splice(0)) logger[method](...args);
    }).catch(error =>
    {
        failed = true;
        console.error('[DEBUG]', error);
        for (const [method, args] of pending.splice(0))
        {
            if (method === 'logError') console.error(...args);
        }
    });
}
function write(method, args)
{
    if (logger) logger[method](...args);
    else if (failed)
    {
        if (method === 'logError') console.error(...args);
    }
    else
    {
        if (pending.length >= 500) pending.shift();
        pending.push([method, args]);
    }
}
export function debug(scope, ...args)
{
    if (config.debug) write('logInfo', [scope, ...args]);
}
export function debugError(scope, error, ...args)
{
    if (config.debug) write('logError', [scope, error, ...args]);
    else console.error(`[${scope}]`, error, ...args);
}
