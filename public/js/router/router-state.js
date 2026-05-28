// =========================================
// ROUTER STATE
// =========================================

export const navigationState =
{
    locked:
        false,

    navigationId:
        0,

    controller:
        null,
};

// =========================================
// LOCK
// =========================================

export function lockRouter()
{
    navigationState.locked =
        true;
}

// =========================================
// UNLOCK
// =========================================

export function unlockRouter()
{
    navigationState.locked =
        false;
}

// =========================================
// CONTROLLER
// =========================================

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