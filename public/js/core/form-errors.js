export function formErrorMessage(error)
{
    const errors = error?.details?.data?.errors;
    if (errors && typeof errors === 'object')
    {
        const message = Object.values(errors).find(value => typeof value === 'string' && value.trim() !== '');
        if (message) return message;
    }
    return error?.message || 'Erreur serveur';
}
