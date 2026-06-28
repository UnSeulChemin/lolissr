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
} from '../search/controller/search-controller.js';

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

import {
    initSqlPage,
} from '../sql/pages/sql.js';

import {
    initProfileCustomization,
} from '../profil/profile-customization.js';

import {
    initAjouterPage as initAjouterFigurinePage,
} from '../figurine/pages/ajouter.js';

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
    | AJOUTER FIGURINE
    |--------------------------------------------------------------------------
    */

    {
        match:
            /^\/lolissr\/figurines\/ajouter\/?$/,

        initializers:
        [
            [
                'AjouterFigurinePage',
                initAjouterFigurinePage,
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
            /^\/lolissr\/chinois\/ajouter\/(grammaire|vocabulaire)\/?$/,

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
            /^\/lolissr\/manga\/series\/.+\/modifier\/\d+\/?$/,

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
    | SQL
    |--------------------------------------------------------------------------
    */

    {
        match:
            /^\/lolissr\/sql\/?$/,

        initializers:
        [
            [
                'SqlPage',
                initSqlPage,
            ],
        ],
    },

    {
        match:
            /^\/lolissr\/profil\/personnalisation\/?$/,

        initializers:
        [
            [
                'ProfileCustomization',
                initProfileCustomization,
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