// ==================================================
// ROUTE INITIALIZERS
// ==================================================

// ==================================================
// LAZY INITIALIZER
// ==================================================

function lazyInitializer(
    loadModule,
    exportName,
    selector = null,
)
{
    let pending;
    const preload = () => pending ??= loadModule().catch(error =>
    {
        pending = undefined;
        throw error;
    });
    const init = async () =>
    {
        const module =
            await preload();

        const initializer =
            module[exportName];

        if (typeof initializer !== 'function')
        {
            throw new TypeError(
                `Initialiseur "${exportName}" introuvable.`,
            );
        }

        await initializer();
    };
    init.preload = preload;
    init.isRelevant = () => selector === null || document.querySelector(selector) !== null;
    return init;
}

// ==================================================
// MANGA INITIALIZERS
// ==================================================

const initAjouterMangaPage = lazyInitializer(
    () => import('../manga/pages/ajouter.js'),
    'initAjouterPage',
);

const initModifierMangaPage = lazyInitializer(
    () => import('../manga/pages/modifier.js'),
    'initModifierPage',
);

const initUpdateNote = lazyInitializer(
    () => import('../manga/actions/update-note.js'),
    'initUpdateNote',
    '.js-note-button',
);

const initDeleteManga = lazyInitializer(
    () => import('../manga/actions/delete-manga.js'),
    'initDeleteManga',
    '.js-delete-manga',
);

const initDeleteArtbook = lazyInitializer(
    () => import('../manga/actions/delete-artbook.js'),
    'initDeleteArtbook',
    '.js-delete-artbook',
);

const initUpdateReadStatus = lazyInitializer(
    () => import('../manga/actions/update-read-status.js'),
    'initUpdateReadStatus',
    '.js-read-status-button',
);

// ==================================================
// FIGURINE INITIALIZERS
// ==================================================

const initAjouterFigurinePage = lazyInitializer(
    () => import('../figurine/pages/ajouter.js'),
    'initAjouterPage',
);

const initDeleteFigurine = lazyInitializer(
    () => import('../figurine/actions/delete-figurine.js'),
    'initDeleteFigurine',
    '.js-delete-figurine',
);

const initUpdateFigurineCollectStatus = lazyInitializer(
    () => import('../figurine/actions/update-collect-status.js'),
    'initUpdateCollectStatus',
    '.js-figurine-collect-status-button',
);

// ==================================================
// PELUCHE INITIALIZERS
// ==================================================

const initAjouterPeluchePage = lazyInitializer(
    () => import('../peluche/pages/ajouter.js'),
    'initAjouterPage',
);

const initDeletePeluche = lazyInitializer(
    () => import('../peluche/actions/delete-peluche.js'),
    'initDeletePeluche',
    '.js-delete-peluche',
);

const initUpdatePelucheCollectStatus = lazyInitializer(
    () => import('../peluche/actions/update-collect-status.js'),
    'initUpdatePelucheCollectStatus',
    '.js-peluche-collect-status-button',
);

// ==================================================
// NENDOROID INITIALIZERS
// ==================================================

const initAjouterNendoroidPage = lazyInitializer(
    () => import('../nendoroid/pages/ajouter.js'),
    'initAjouterPage',
);

const initDeleteNendoroid = lazyInitializer(
    () => import('../nendoroid/actions/delete-nendoroid.js'),
    'initDeleteNendoroid',
    '.js-delete-nendoroid',
);

const initUpdateNendoroidCollectStatus = lazyInitializer(
    () => import('../nendoroid/actions/update-collect-status.js'),
    'initUpdateNendoroidCollectStatus',
    '.js-nendoroid-collect-status-button',
);

// ==================================================
// CHINOIS INITIALIZERS
// ==================================================

const initAjouterChinoisPage = lazyInitializer(
    () => import('../chinois/pages/ajouter.js'),
    'initAjouterPage',
);

const initFlashcardsVocabulairePage = lazyInitializer(
    () => import('../chinois/pages/flashcards-vocabulaire.js'),
    'initFlashcardsVocabulairePage',
);

const initFlashcardsGrammairePage = lazyInitializer(
    () => import('../chinois/pages/flashcards-grammaire.js'),
    'initFlashcardsGrammairePage',
);

const initToggleGrammaireMaitrise = lazyInitializer(
    () => import('../chinois/actions/toggle-grammar-mastery.js'),
    'initToggleGrammaireMaitrise',
    '.grammar-ajax',
);

const initToggleVocabulaireMaitrise = lazyInitializer(
    () => import('../chinois/actions/toggle-vocabulary-mastery.js'),
    'initToggleVocabulaireMaitrise',
    '.vocabulary-ajax',
);

const initDeleteGrammaire = lazyInitializer(
    () => import('../chinois/actions/delete-grammar.js'),
    'initDeleteGrammaire',
    '.grammaire-delete',
);

const initDeleteVocabulaire = lazyInitializer(
    () => import('../chinois/actions/delete-vocabulary.js'),
    'initDeleteVocabulaire',
    '.vocabulaire-delete',
);

// ==================================================
// PROFILE INITIALIZERS
// ==================================================

const initProfileCustomization = lazyInitializer(
    () => import('../profil/profile-customization.js'),
    'initProfileCustomization',
);

// ==================================================
// SQL INITIALIZERS
// ==================================================

const initSqlPage = lazyInitializer(
    () => import('../sql/pages/sql.js'),
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
