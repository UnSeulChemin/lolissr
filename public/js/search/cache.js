const entries = new Map();
const CACHE_DURATION = 30000;
const CACHE_LIMIT = 20;
let generation = 0;

export function invalidateSearchCache()
{
    generation++;
    entries.clear();
    document.dispatchEvent(new CustomEvent('search:invalidate'));
}

export async function cachedSearch(url, signal, load)
{
    if (signal?.aborted) throw new DOMException('Search aborted', 'AbortError');
    const key = new URL(url, location.href).href;
    const cached = entries.get(key);
    if (cached && Date.now() - cached.timestamp < CACHE_DURATION)
    {
        entries.delete(key);
        entries.set(key, cached);
        return cached.data;
    }
    entries.delete(key);
    const startedGeneration = generation;
    const data = await load();
    if (signal?.aborted || generation !== startedGeneration)
        throw new DOMException('Search invalidated', 'AbortError');
    entries.set(key, {data, timestamp: Date.now()});
    while (entries.size > CACHE_LIMIT) entries.delete(entries.keys().next().value);
    return data;
}
