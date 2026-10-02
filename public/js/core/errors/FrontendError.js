// =================================================
// INTERFACE ERREUR
// =================================================

export class FrontendError extends Error
{
    constructor(
        message,
        options = {},
    )
    {
        super(message);

        this.name =
            'FrontendError';

        this.code =
            options.code
            || 'FRONTEND_ERROR';

        this.status =
            options.status
            || 500;

        this.silent =
            options.silent
            || false;

        this.details =
            options.details
            || null;

        // --------------------------------------------------------------------------
        // CORRECTION DE LA TRACE D’APPEL
        // --------------------------------------------------------------------------


        if (
            typeof Error.captureStackTrace
            === 'function'
        ) {

            Error.captureStackTrace(
                this,
                FrontendError,
            );
        }
    }
}