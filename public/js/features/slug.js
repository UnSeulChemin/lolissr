/**
 * Génère un slug propre à partir d'un texte.
 */
function generateSlugFromString(value)
{
    return value
        .toLowerCase()
        .trim()

        /* Supprime caractères non autorisés */
        .replace(/[^a-z0-9\s-]/g, '')

        /* Espaces → tirets */
        .replace(/\s+/g, '-')

        /* Tirets multiples */
        .replace(/-+/g, '-')

        /* Tirets début / fin */
        .replace(/^-+|-+$/g, '');
}

export function initAutoSlug()
{
    /*
    |------------------------------------------------------------------
    | Sélection des champs
    |------------------------------------------------------------------
    */

    const livreInput = document.getElementById('livre');
    const slugInput = document.getElementById('slug');

    if (!livreInput || !slugInput)
    {
        return;
    }

    /*
    |------------------------------------------------------------------
    | Protection anti double init
    |------------------------------------------------------------------
    */

    if (slugInput.dataset.autoSlugInit === 'true')
    {
        return;
    }

    slugInput.dataset.autoSlugInit = 'true';

    /*
    |------------------------------------------------------------------
    | État
    |------------------------------------------------------------------
    */

    let slugWasEditedManually = false;

    /*
    |------------------------------------------------------------------
    | Si modification manuelle du slug
    |------------------------------------------------------------------
    */

    slugInput.addEventListener('input', () =>
    {
        slugWasEditedManually = true;
    });

    /*
    |------------------------------------------------------------------
    | Génération automatique depuis "livre"
    |------------------------------------------------------------------
    */

    livreInput.addEventListener('input', () =>
    {
        if (slugWasEditedManually)
        {
            return;
        }

        slugInput.value = generateSlugFromString(
            livreInput.value
        );
    });
}