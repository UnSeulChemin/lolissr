// =================================================
// DÉBOGAGE
// =================================================

import { config } from '../config.js';

// =================================================
// DÉMARRAGE
// =================================================

export function start(name)
{
    if (!config.debug)
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
    if (!config.debug)
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
    if (!config.debug)
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
    if (!config.debug)
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
    if (!config.debug)
    {
        return;
    }

    end('total');

    print();

    performance.clearMarks();
    performance.clearMeasures();
}
