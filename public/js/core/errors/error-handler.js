// =================================================
// GESTION DES ERREURS
// =================================================

import {
    showToast,
} from '../toast.js';

import {
    debugError,
} from '../debug/debug.js';

import {
    FrontendError,
} from './FrontendError.js';

// =================================================
// ÉTAT
// =================================================

let lastMessage =
    null;

let lastTimestamp =
    0;

// =================================================
// UTILITAIRES
// =================================================

function shouldSkipDuplicateToast(
    message,
)
{
    const now =
        Date.now();

    const isDuplicate =
        lastMessage === message
        && (
            now
            - lastTimestamp
            < 2000
        );

    lastMessage =
        message;

    lastTimestamp =
        now;

    return isDuplicate;
}

function normalizeError(
    error,
)
{
    // --------------------------------------------------------------------------
    // INTERFACE ERREUR
    // --------------------------------------------------------------------------


    if (
        error
        instanceof FrontendError
    ) {

        return error;
    }

    // --------------------------------------------------------------------------
    // NATIVE ERREUR
    // --------------------------------------------------------------------------


    if (
        error
        instanceof Error
    ) {

        return new FrontendError(
            error.message,
            {
                code:
                    error.name
                    || 'ERROR',
            },
        );
    }

    // --------------------------------------------------------------------------
    // CHAÎNE
    // --------------------------------------------------------------------------


    if (
        typeof error
        === 'string'
    ) {

        return new FrontendError(
            error,
            {
                code:
                    'STRING_ERROR',
            },
        );
    }

    // --------------------------------------------------------------------------
    // INCONNUE
    // --------------------------------------------------------------------------


    return new FrontendError(
        'Une erreur est survenue',
        {
            code:
                'UNKNOWN_ERROR',
        },
    );
}

function getErrorMessage(
    error,
)
{
    switch (
        error.code
    ) {

        case 'NETWORK_ERROR':
            return 'Erreur réseau';

        case 'REQUEST_TIMEOUT':
            return 'Le serveur met trop de temps à répondre';

        case 'HTTP_404':
            return 'Page introuvable';

        case 'HTTP_500':
            return 'Erreur serveur';

        default:
            return (
                error.message
                || 'Une erreur est survenue'
            );
    }
}

// =================================================
// TRAITEMENT ERREUR
// =================================================

export function handleError(
    rawError,
)
{
    const error =
        normalizeError(
            rawError,
        );

    // --------------------------------------------------------------------------
    // DÉBOGAGE
    // --------------------------------------------------------------------------


    debugError(
        'ERROR_HANDLER',
        error,
    );

    // --------------------------------------------------------------------------
    // SILENCIEUX
    // --------------------------------------------------------------------------


    if (
        error.silent
    ) {

        return;
    }

    // --------------------------------------------------------------------------
    // MESSAGE
    // --------------------------------------------------------------------------


    const message =
        getErrorMessage(
            error,
        );

    // --------------------------------------------------------------------------
    // DOUBLON NOTIFICATION
    // --------------------------------------------------------------------------


    if (
        shouldSkipDuplicateToast(
            message,
        )
    ) {

        return;
    }

    // --------------------------------------------------------------------------
    // NOTIFICATION
    // --------------------------------------------------------------------------


    showToast(
        message,
        'error',
    );
}