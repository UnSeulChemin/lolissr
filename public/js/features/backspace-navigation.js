function isTypingTarget(target)
{
    return target instanceof HTMLInputElement
        || target instanceof HTMLTextAreaElement
        || target instanceof HTMLSelectElement
        || target?.isContentEditable;
}

export function initBackspaceNavigation()
{
    document.addEventListener('keydown', (event) =>
    {
        if (event.key !== 'Backspace')
        {
            return;
        }

        if (isTypingTarget(event.target))
        {
            return;
        }

        event.preventDefault();

        history.back();
    });
}