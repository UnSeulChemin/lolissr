// =========================================
// ROUTER FOCUS
// =========================================

export function clearActiveFocus()
{
    if (
        document.activeElement
        instanceof HTMLElement
    ) {

        document.activeElement.blur();
    }
}