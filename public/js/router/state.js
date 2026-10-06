// =================================================
// ÉTAT DU ROUTEUR
// =================================================

export const navigationState = {
    locked: false,

    navigationId: 0,

    controller: null,

    target: null
};

// =================================================
// VERROU
// =================================================

export function lockRouter()
{
    navigationState.locked = true;
}

// =================================================
// DÉBLOCAGE
// =================================================

export function unlockRouter()
{
    navigationState.locked = false;
}

// =================================================
// CONTRÔLEUR
// =================================================

export function setController(controller, target)
{
    navigationState.controller = controller;
    navigationState.target = target;
}

export function clearController()
{
    navigationState.controller = null;
    navigationState.target = null;
}
