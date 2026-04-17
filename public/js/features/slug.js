function generateSlug(value)
{
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');
}

export function initAutoSlug()
{
    const livreInput = document.getElementById('livre');
    const slugInput = document.getElementById('slug');

    if (!livreInput || !slugInput)
    {
        return;
    }

    let slugManuallyEdited = false;

    slugInput.addEventListener('input', () =>
    {
        slugManuallyEdited = true;
    });

    livreInput.addEventListener('input', () =>
    {
        if (slugManuallyEdited)
        {
            return;
        }

        slugInput.value = generateSlug(livreInput.value);
    });
}