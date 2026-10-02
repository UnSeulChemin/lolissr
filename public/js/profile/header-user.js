// =========================================
// HEADER USER
// =========================================

export function updateHeaderUser(
    level,
)
{
    const element =
        document.querySelector(
            '.js-user-level',
        );

    if (
        !element
        || level === undefined
    ) {
        return;
    }

    element.textContent =
        String(level);
}