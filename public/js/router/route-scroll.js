const scrollPositions = new Map();
const stateKey = '__appScrollEntry';
let activeEntry = null;

// Pendant popstate, l’adresse indique déjà la destination ; suivre l’entrée affichée.
export function activateScrollEntry()
{
    let entry = history.state?.[stateKey];
    if (typeof entry !== 'string')
    {
        entry = globalThis.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random()}`;
        history.replaceState({...history.state, [stateKey]: entry}, '');
    }
    activeEntry = entry;
}

export function saveScrollPosition()
{
    if (activeEntry === null) activateScrollEntry();
    scrollPositions.set(activeEntry, {x: window.scrollX, y: window.scrollY});
}

export function restoreScrollPosition()
{
    const entry = activeEntry;
    const position = scrollPositions.get(entry) ?? {x: 0, y: 0};
    requestAnimationFrame(() =>
    {
        if (activeEntry === entry) window.scrollTo(position.x, position.y);
    });
}
