// =========================================
// FRONTEND ERROR
// =========================================

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

        /*
        |--------------------------------------------------------------------------
        | STACK TRACE FIX
        |--------------------------------------------------------------------------
        */

        if (
            Error.captureStackTrace
        ) {

            Error.captureStackTrace(
                this,
                FrontendError,
            );
        }
    }
}