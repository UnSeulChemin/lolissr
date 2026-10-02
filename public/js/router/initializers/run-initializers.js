// Fetch independent modules together, then preserve their initialization order.
export async function runInitializers(initializers, safeInit, isCurrent = () => true)
{
    const relevant = initializers.filter(([, init]) => init.isRelevant?.() ?? true);
    const loaded = await Promise.allSettled(relevant.map(([, init]) => init.preload?.()));
    for (let index = 0; index < relevant.length; index++)
    {
        if (!isCurrent()) return;
        const [label, init] = relevant[index];
        await safeInit(label, () =>
        {
            if (loaded[index].status === 'rejected') throw loaded[index].reason;
            return init();
        });
    }
}
