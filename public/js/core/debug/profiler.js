// =================================================
// DÉBOGAGE
// =================================================

import { isDebugEnabled } from './debug-storage.js';

// =================================================
// DÉMARRAGE
// =================================================

export function start(name)
{
    if (!isDebugEnabled())
    {
        return;
    }

    performance.mark(`${name}-start`);
}

// =================================================
// FIN
// =================================================

export function end(name)
{
    if (!isDebugEnabled())
    {
        return;
    }

    performance.mark(`${name}-end`);

    performance.measure(name, `${name}-start`, `${name}-end`);
}

// =================================================
// AFFICHAGE
// =================================================

export function print()
{
    if (!isDebugEnabled())
    {
        return;
    }

    const measures = performance.getEntriesByType('measure');

    console.table(
        measures.map(
            ({
                name,
                duration
            }) => ({
                Étape: name,

                Temps: `${duration.toFixed(2)} ms`
            })
        )
    );
}

// =================================================
// RÉINITIALISATION
// =================================================

export function reset()
{
    if (!isDebugEnabled())
    {
        return;
    }

    performance.clearMarks();
    performance.clearMeasures();
}

// =================================================
// FIN
// =================================================

export function finish()
{
    if (!isDebugEnabled())
    {
        return;
    }

    end('total');

    print();

    performance.clearMarks();
    performance.clearMeasures();
}