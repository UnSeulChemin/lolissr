// =================================================
// VALIDATION PAGE RÉPONSE
// =================================================

import { FrontendError } from '../../core/errors/frontend-error.js';

// =================================================
// VALIDATION
// =================================================

export function validatePageResponse(response)
{
    if (response?.type !== 'page')
    {

        throw new FrontendError(
            'Réponse page invalide',
            {
                code: 'INVALID_PAGE_RESPONSE'
            }
        );
    }

    if (typeof response.page?.html !== 'string')
    {

        throw new FrontendError(
            'HTML page invalide',
            {
                code: 'INVALID_PAGE_HTML'
            }
        );
    }
}