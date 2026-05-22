/**
* Génère un slug propre à partir d'un texte.
*/
export function generateSlug(value)
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
/**
* Normalise une valeur de recherche pour l'URL.
*/
export function normalizeSearchQuery(value)
{
return value
.toLowerCase()
.normalize('NFD')
.replace(/[\u0300-\u036f]/g, '')
.replace(/[^a-z0-9\s-]/g, '')
.replace(/\s+/g, '-')
.replace(/-+/g, '-')
.replace(/^-+|-+$/g, '');
}