// =================================================
// ÉTAT DU ROUTEUR
// =================================================

export const navigationState =
{
    locked:
        false,

    navigationId:
        0,

    controller:
        null,
};

// =================================================
// VERROU
// =================================================

export function lockRouter()
{
    navigationState.locked =
        true;
}

// =================================================
// DÉBLOCAGE
// =================================================

export function unlockRouter()
{
    navigationState.locked =
        false;
}

// =================================================
// CONTRÔLEUR
// =================================================

export function setController(
    controller,
)
{
    navigationState.controller =
        controller;
}

export function clearController()
{
    navigationState.controller =
        null;
}