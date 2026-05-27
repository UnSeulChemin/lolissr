const cleanupCallbacks = [];

export function registerCleanup(
    callback,
)
{
    cleanupCallbacks.push(
        callback,
    );
}

export function runCleanup()
{
    while (
        cleanupCallbacks.length
    )
    {
        const callback =
            cleanupCallbacks.pop();

        callback?.();
    }
}