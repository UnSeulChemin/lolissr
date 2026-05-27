// =========================================
// CORE : DOM
// =========================================

// =========================================
// STATE
// =========================================

const delegatedEvents =
    new Set();

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
    // UNIQUE KEY
    // =====================================

    const key =
        `${eventType}::${selector}`;

    // =====================================
    // ALREADY REGISTERED
    // =====================================

    if (
        delegatedEvents.has(
            key,
        )
    ) {
        return;
    }

    delegatedEvents.add(
        key,
    );

    parent.addEventListener(
        eventType,
        (event) =>
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