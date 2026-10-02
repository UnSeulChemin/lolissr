// =================================================
// SOCLE : DOM
// =================================================

// =================================================
// DÉLÉGUÉS ÉVÉNEMENTS
// =================================================

const delegatedEvents =
    new WeakMap();

// =================================================
// REQUÊTE
// =================================================

export function $(
    selector,
    parent = document,
)
{
    return parent.querySelector(
        selector,
    );
}

// =================================================
// SÉLECTION MULTIPLE
// =================================================

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

// =================================================
// LECTURE DES ATTRIBUTS DE DONNÉES
// =================================================

export function data(
    element,
    key,
)
{
    return element.dataset[
        key
    ];
}

// =================================================
// DÉLÉGATION
// =================================================

export function delegate(
    parent,
    eventType,
    selector,
    callback,
)
{
    // =================================================
    // STOCKAGE PARENT
    // =================================================

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

    // =================================================
    // UNIQUE CLÉ
    // =================================================

    const key =
        `${eventType}::${selector}`;

    // =================================================
    // DÉJÀ ENREGISTRÉ
    // =================================================

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

    // =================================================
    // ÉCOUTEUR
    // =================================================

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
