// =========================================
// ROUTE HOOKS
// =========================================

const hooks =
{
    beforeEnter: [],
    afterEnter: [],
};

// =========================================
// REGISTER
// =========================================

export function onBeforeRouteChange(
    callback,
)
{
    hooks.beforeEnter.push(
        callback,
    );
}

export function onRouteChange(
    callback,
)
{
    hooks.afterEnter.push(
        callback,
    );
}

// =========================================
// TRIGGER
// =========================================

export async function triggerBeforeRouteChange(
    context,
)
{
    for (const callback of hooks.beforeEnter)
    {
        await callback(
            context,
        );
    }
}

export async function triggerRouteChange(
    context,
)
{
    for (const callback of hooks.afterEnter)
    {
        await callback(
            context,
        );
    }
}