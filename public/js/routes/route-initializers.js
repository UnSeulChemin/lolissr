// ==================================================
// ROUTE INITIALIZERS
// ==================================================

import {
    initAjouterPage,
} from '../manga/pages/ajouter.js';

import {
    initModifierPage,
} from '../manga/pages/modifier.js';

import {
    initUpdateNote,
} from '../manga/actions/update-note.js';

import {
    initDeleteManga,
} from '../manga/actions/delete-manga.js';

import {
    initUpdateReadStatus,
} from '../manga/actions/update-read-status.js';

import {
    initSearchController,
} from '../manga/search/controller/search-controller.js';

import {
    initToggleGrammaireMaitrise,
} from '../chinois/actions/toggle-grammar-mastery.js';

import {
    initToggleVocabulaireMaitrise,
} from '../chinois/actions/toggle-vocabulary-mastery.js';

import {
    initDeleteGrammaire,
} from '../chinois/actions/delete-grammar.js';

import {
    initAjouterPage as initAjouterChinoisPage,
} from '../chinois/pages/ajouter.js';

import {
    initDeleteVocabulaire,
} from '../chinois/actions/delete-vocabulary.js';

import {
    initFlashcardsVocabulairePage,
} from '../chinois/pages/flashcards-vocabulaire.js';

import {
    initFlashcardsGrammairePage,
} from '../chinois/pages/flashcards-grammaire.js';


// ==================================================
// EXPORT
// ==================================================

export const ROUTE_INITIALIZERS = [

    /*
    |--------------------------------------------------------------------------
    | AJOUTER MANGA
    |--------------------------------------------------------------------------
    */

    {
        match:
            /^\/lolissr\/manga\/ajouter\/?$/,

        initializers:
        [
            [
                'AjouterPage',
                initAjouterPage,
            ],
        ],
    },

    /*
    |--------------------------------------------------------------------------
    | AJOUTER CHINOIS
    |--------------------------------------------------------------------------
    */

    {
        match:
            /^\/lolissr\/chinois\/(grammaire|vocabulaire)\/ajouter\/?$/,

        initializers:
        [
            [
                'AjouterChinoisPage',
                initAjouterChinoisPage,
            ],
        ],
    },

    {
        match:
            /^\/lolissr\/chinois\/flashcards\/vocabulaire\/?$/,

        initializers:
        [
            [
                'FlashcardsVocabulaire',
                initFlashcardsVocabulairePage,
            ],
        ],
    },

    {
        match:
            /^\/lolissr\/chinois\/flashcards\/grammaire\/?$/,

        initializers:
        [
            [
                'FlashcardsGrammaire',
                initFlashcardsGrammairePage,
            ],
        ],
    },

    /*
    |--------------------------------------------------------------------------
    | MODIFIER MANGA
    |--------------------------------------------------------------------------
    */

    {
        match:
            /^\/lolissr\/manga\/series\/modifier\/.+/,

        initializers:
        [
            [
                'ModifierPage',
                initModifierPage,
            ],
        ],
    },

    /*
    |--------------------------------------------------------------------------
    | GLOBAL ROUTES
    |--------------------------------------------------------------------------
    */

    {
        match:
            /^\/lolissr\/?/,

        initializers:
        [
            [
                'UpdateNote',
                initUpdateNote,
            ],

            [
                'DeleteManga',
                initDeleteManga,
            ],

            [
                'UpdateReadStatus',
                initUpdateReadStatus,
            ],

            [
                'SearchController',
                initSearchController,
            ],

            [
                'ToggleGrammaireMaitrise',
                initToggleGrammaireMaitrise,
            ],

            [
                'ToggleVocabulaireMaitrise',
                initToggleVocabulaireMaitrise,
            ],

            [
                'DeleteGrammaire',
                initDeleteGrammaire,
            ],

            [
                'DeleteVocabulaire',
                initDeleteVocabulaire,
            ],
        ],
    },
];