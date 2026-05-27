// =========================================
// CORE : DOM
// =========================================

// =========================================
// DELEGATED EVENTS
// =========================================

const delegatedEvents =
    new WeakMap();

// =========================================
// QUERY
// =========================================

export function $(
    selector,
    parent = document,
)
{
    return parent.querySelector(
        selector,
    );
}

// =========================================
// QUERY ALL
// =========================================

export function $$(
    selector,
    parent = document,
)
{
    return [
        ...parent.querySelectorAll(
            selector,
        ),
    ];
}

// =========================================
// EXISTS
// =========================================

export function exists(
    selector,
    parent = document,
)
{
    return Boolean(
        $(
            selector,
            parent,
        ),
    );
}

// =========================================
// DATASET GET
// =========================================

export function data(
    element,
    key,
)
{
    return element.dataset[
        key
    ];
}

// =========================================
// DATASET SET
// =========================================

export function setData(
    element,
    key,
    value,
)
{
    element.dataset[
        key
    ] = value;
}

// =========================================
// DELEGATE
// =========================================

export function delegate(
    parent,
    eventType,
    selector,
    callback,
)
{
    // =====================================
    // PARENT STORE
    // =====================================

    if (
        !delegatedEvents.has(
            parent,
        )
    ) {

        delegatedEvents.set(
            parent,
            new Set(),
        );
    }

    const parentEvents =
        delegatedEvents.get(
            parent,
        );

    // =====================================
    // UNIQUE KEY
    // =====================================

    const key =
        `${eventType}::${selector}`;

    // =====================================
    // ALREADY REGISTERED
    // =====================================

    if (
        parentEvents.has(
            key,
        )
    ) {
        return;
    }

    parentEvents.add(
        key,
    );

    // =====================================
    // LISTENER
    // =====================================

    parent.addEventListener(
        eventType,
        (
            event,
        ) =>
        {
            const target =
                event.target;

            if (
                !(
                    target
                    instanceof Element
                )
            ) {
                return;
            }

            const element =
                target.closest(
                    selector,
                );

            if (!element) {
                return;
            }

            callback(
                event,
                element,
            );
        },
    );
}