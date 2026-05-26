// =========================================
// CORE : DOM
// =========================================

/**
 * Query Selector
 */
export function $(
    selector,
    parent = document,
)
{
    return parent.querySelector(selector);
}

/**
 * Query Selector All
 */
export function $$(
    selector,
    parent = document,
)
{
    return [
        ...parent.querySelectorAll(selector),
    ];
}

/**
 * Safe Event Delegation
 */
export function delegate(
    parent,
    eventType,
    selector,
    callback,
)
{
    parent.addEventListener(eventType, (event) =>
    {
        const element =
            event.target instanceof Element
                ? event.target.closest(selector)
                : null;

        if (!element)
        {
            return;
        }

        callback(event, element);
    });
}

/**
 * Dataset Getter
 */
export function data(
    element,
    key,
)
{
    return element.dataset[key];
}

/**
 * Dataset Setter
 */
export function setData(
    element,
    key,
    value,
)
{
    element.dataset[key] = value;
}

/**
 * Element Exists
 */
export function exists(
    selector,
    parent = document,
)
{
    return !!$(selector, parent);
}