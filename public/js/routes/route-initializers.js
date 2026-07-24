// ==================================================
// ROUTE INITIALIZERS
// ==================================================

// ==================================================
// LAZY INITIALIZER
// ==================================================

function lazyInitializer(
    modulePath,
    exportName,
)
{
    return async () =>
    {
        const module =
            await import(modulePath);

        const initializer =
            module[exportName];

        if (typeof initializer !== 'function')
        {
            throw new TypeError(
                `Initialiseur "${exportName}" introuvable dans "${modulePath}".`,
            );
        }

        await initializer();
    };
}

// ==================================================
// MANGA INITIALIZERS
// ==================================================

const initAjouterMangaPage = lazyInitializer(
    '../manga/pages/ajouter.js',
    'initAjouterPage',
);

const initModifierMangaPage = lazyInitializer(
    '../manga/pages/modifier.js',
    'initModifierPage',
);

const initUpdateNote = lazyInitializer(
    '../manga/actions/update-note.js',
    'initUpdateNote',
);

const initDeleteManga = lazyInitializer(
    '../manga/actions/delete-manga.js',
    'initDeleteManga',
);

const initDeleteArtbook = lazyInitializer(
    '../manga/actions/delete-artbook.js',
    'initDeleteArtbook',
);

const initUpdateReadStatus = lazyInitializer(
    '../manga/actions/update-read-status.js',
    'initUpdateReadStatus',
);

// ==================================================
// FIGURINE INITIALIZERS
// ==================================================

const initAjouterFigurinePage = lazyInitializer(
    '../figurine/pages/ajouter.js',
    'initAjouterPage',
);

const initDeleteFigurine = lazyInitializer(
    '../figurine/actions/delete-figurine.js',
    'initDeleteFigurine',
);

const initUpdateFigurineCollectStatus = lazyInitializer(
    '../figurine/actions/update-collect-status.js',
    'initUpdateCollectStatus',
);

// ==================================================
// PELUCHE INITIALIZERS
// ==================================================

const initAjouterPeluchePage = lazyInitializer(
    '../peluche/pages/ajouter.js',
    'initAjouterPage',
);

const initDeletePeluche = lazyInitializer(
    '../peluche/actions/delete-peluche.js',
    'initDeletePeluche',
);

const initUpdatePelucheCollectStatus = lazyInitializer(
    '../peluche/actions/update-collect-status.js',
    'initUpdatePelucheCollectStatus',
);

// ==================================================
// NENDOROID INITIALIZERS
// ==================================================

const initAjouterNendoroidPage = lazyInitializer(
    '../nendoroid/pages/ajouter.js',
    'initAjouterPage',
);

const initDeleteNendoroid = lazyInitializer(
    '../nendoroid/actions/delete-nendoroid.js',
    'initDeleteNendoroid',
);

const initUpdateNendoroidCollectStatus = lazyInitializer(
    '../nendoroid/actions/update-collect-status.js',
    'initUpdateNendoroidCollectStatus',
);

// ==================================================
// CHINOIS INITIALIZERS
// ==================================================

const initAjouterChinoisPage = lazyInitializer(
    '../chinois/pages/ajouter.js',
    'initAjouterPage',
);

const initFlashcardsVocabulairePage = lazyInitializer(
    '../chinois/pages/flashcards-vocabulaire.js',
    'initFlashcardsVocabulairePage',
);

const initFlashcardsGrammairePage = lazyInitializer(
    '../chinois/pages/flashcards-grammaire.js',
    'initFlashcardsGrammairePage',
);

const initToggleGrammaireMaitrise = lazyInitializer(
    '../chinois/actions/toggle-grammar-mastery.js',
    'initToggleGrammaireMaitrise',
);

const initToggleVocabulaireMaitrise = lazyInitializer(
    '../chinois/actions/toggle-vocabulary-mastery.js',
    'initToggleVocabulaireMaitrise',
);

const initDeleteGrammaire = lazyInitializer(
    '../chinois/actions/delete-grammar.js',
    'initDeleteGrammaire',
);

const initDeleteVocabulaire = lazyInitializer(
    '../chinois/actions/delete-vocabulary.js',
    'initDeleteVocabulaire',
);

// ==================================================
// PROFILE INITIALIZERS
// ==================================================

const initProfileCustomization = lazyInitializer(
    '../profil/profile-customization.js',
    'initProfileCustomization',
);

// ==================================================
// SQL INITIALIZERS
// ==================================================

const initSqlPage = lazyInitializer(
    '../sql/pages/sql.js',
    'initSqlPage',
);

// ==================================================
// EXPORT
// ==================================================

export const ROUTE_INITIALIZERS = [
    /*
    |--------------------------------------------------------------------------
    | MANGA
    |--------------------------------------------------------------------------
    */

    {
        match: /^\/manga(?:\/|$)/,

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
                'DeleteArtbook',
                initDeleteArtbook,
            ],
            [
                'UpdateReadStatus',
                initUpdateReadStatus,
            ],
        ],
    },

    {
        match: /^\/manga\/ajouter\/(manga|artbook)\/?$/,

        initializers:
        [
            [
                'AjouterMangaPage',
                initAjouterMangaPage,
            ],
        ],
    },

    {
        match: /^\/manga\/series\/.+\/modifier\/\d+\/?$/,

        initializers:
        [
            [
                'ModifierMangaPage',
                initModifierMangaPage,
            ],
        ],
    },

    /*
    |--------------------------------------------------------------------------
    | FIGURINE
    |--------------------------------------------------------------------------
    */

    {
        match: /^\/figurine(?:\/|$)/,

        initializers:
        [
            [
                'DeleteFigurine',
                initDeleteFigurine,
            ],
            [
                'UpdateFigurineCollectStatus',
                initUpdateFigurineCollectStatus,
            ],
        ],
    },

    {
        match: /^\/figurine\/ajouter\/?$/,

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
    | PELUCHE
    |--------------------------------------------------------------------------
    */

    {
        match: /^\/peluche(?:\/|$)/,

        initializers:
        [
            [
                'DeletePeluche',
                initDeletePeluche,
            ],
            [
                'UpdatePelucheCollectStatus',
                initUpdatePelucheCollectStatus,
            ],
        ],
    },

    {
        match: /^\/peluche\/ajouter\/?$/,

        initializers:
        [
            [
                'AjouterPeluchePage',
                initAjouterPeluchePage,
            ],
        ],
    },

    /*
    |--------------------------------------------------------------------------
    | NENDOROID
    |--------------------------------------------------------------------------
    */

    {
        match: /^\/nendoroid(?:\/|$)/,

        initializers:
        [
            [
                'DeleteNendoroid',
                initDeleteNendoroid,
            ],
            [
                'UpdateNendoroidCollectStatus',
                initUpdateNendoroidCollectStatus,
            ],
        ],
    },

    {
        match: /^\/nendoroid\/ajouter\/?$/,

        initializers:
        [
            [
                'AjouterNendoroidPage',
                initAjouterNendoroidPage,
            ],
        ],
    },

    /*
    |--------------------------------------------------------------------------
    | CHINOIS
    |--------------------------------------------------------------------------
    */

    {
        match: /^\/chinois(?:\/|$)/,

        initializers:
        [
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

    {
        match: /^\/chinois\/ajouter\/(grammaire|vocabulaire)\/?$/,

        initializers:
        [
            [
                'AjouterChinoisPage',
                initAjouterChinoisPage,
            ],
        ],
    },

    {
        match: /^\/chinois\/flashcards\/vocabulaire\/?$/,

        initializers:
        [
            [
                'FlashcardsVocabulaire',
                initFlashcardsVocabulairePage,
            ],
        ],
    },

    {
        match: /^\/chinois\/flashcards\/grammaire\/?$/,

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
    | PROFILE
    |--------------------------------------------------------------------------
    */

    {
        match: /^\/profil\/personnalisation\/?$/,

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
    | SQL
    |--------------------------------------------------------------------------
    */

    {
        match: /^\/sql\/?$/,

        initializers:
        [
            [
                'SqlPage',
                initSqlPage,
            ],
        ],
    },
];