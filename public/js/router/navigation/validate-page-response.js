// =========================================
// VALIDATE PAGE RESPONSE
// =========================================

import {
    FrontendError,
} from '../../core/errors/FrontendError.js';

// =========================================
// VALIDATE
// =========================================

export function validatePageResponse(
    response,
)
{
    if (
        response?.type
        !== 'page'
    ) {

        throw new FrontendError(
            'Réponse page invalide',
            {
                code:
                    'INVALID_PAGE_RESPONSE',
            },
        );
    }

    if (
        typeof response.page?.html
        !== 'string'
    ) {

        throw new FrontendError(
            'HTML page invalide',
            {
                code:
                    'INVALID_PAGE_HTML',
            },
        );
    }
}