// =================================================
// ROUTE INITIALISATIONS
// =================================================

// =================================================
// DIFFÉRÉE INITIALISATION
// =================================================

function lazyInitializer(loadModule, exportName, selector = null)
{
    let pending;
    const preload = () => pending ??= loadModule().catch(error =>
    {
        pending = undefined;
        throw error;
    });
    const init = async () =>
    {
        const module = await preload();

        const initializer = module[exportName];

        if (typeof initializer !== 'function')
        {
            throw new TypeError(`Initialiseur "${exportName}" introuvable.`);
        }

        await initializer();
    };
    init.preload = preload;
    init.isRelevant = () => selector === null || document.querySelector(selector) !== null;
    return init;
}

// =================================================
// MANGA INITIALISATIONS
// =================================================

const initCreateMangaPage = lazyInitializer(() => import('../../manga/pages/create.js'), 'initCreatePage');
const initAcquireRelease = lazyInitializer(() => import('../../manga/actions/acquire-release.js'), 'initAcquireRelease');

const initEditMangaPage = lazyInitializer(() => import('../../manga/pages/edit.js'), 'initEditPage');

const initUpdateNote = lazyInitializer(
    () => import('../../manga/actions/update-note.js'),
    'initUpdateNote',
    '.js-note-button'
);

const initDeleteManga = lazyInitializer(
    () => import('../../manga/actions/delete-manga.js'),
    'initDeleteManga',
    '.js-delete-manga'
);

const initDeleteArtbook = lazyInitializer(
    () => import('../../manga/actions/delete-artbook.js'),
    'initDeleteArtbook',
    '.js-delete-artbook'
);

const initUpdateReadStatus = lazyInitializer(
    () => import('../../manga/actions/update-read-status.js'),
    'initUpdateReadStatus',
    '.js-read-status-button'
);

// =================================================
// FIGURINE INITIALISATIONS
// =================================================

const initCreateFigurinePage = lazyInitializer(() => import('../../figurine/pages/create.js'), 'initCreatePage');

const initDeleteFigurine = lazyInitializer(
    () => import('../../figurine/actions/delete-figurine.js'),
    'initDeleteFigurine',
    '.js-delete-figurine'
);

const initUpdateFigurineCollectStatus = lazyInitializer(
    () => import('../../figurine/actions/update-collect-status.js'),
    'initUpdateCollectStatus',
    '.js-figurine-collect-status-button'
);

// =================================================
// PELUCHE INITIALISATIONS
// =================================================

const initCreatePeluchePage = lazyInitializer(() => import('../../peluche/pages/create.js'), 'initCreatePage');

const initDeletePeluche = lazyInitializer(
    () => import('../../peluche/actions/delete-peluche.js'),
    'initDeletePeluche',
    '.js-delete-peluche'
);

const initUpdatePelucheCollectStatus = lazyInitializer(
    () => import('../../peluche/actions/update-collect-status.js'),
    'initUpdatePelucheCollectStatus',
    '.js-peluche-collect-status-button'
);

// =================================================
// NENDOROID INITIALISATIONS
// =================================================

const initCreateNendoroidPage = lazyInitializer(() => import('../../nendoroid/pages/create.js'), 'initCreatePage');

const initDeleteNendoroid = lazyInitializer(
    () => import('../../nendoroid/actions/delete-nendoroid.js'),
    'initDeleteNendoroid',
    '.js-delete-nendoroid'
);

const initUpdateNendoroidCollectStatus = lazyInitializer(
    () => import('../../nendoroid/actions/update-collect-status.js'),
    'initUpdateNendoroidCollectStatus',
    '.js-nendoroid-collect-status-button'
);

// =================================================
// CHINOIS INITIALISATIONS
// =================================================

const initCreateChinoisPage = lazyInitializer(() => import('../../chinois/pages/create.js'), 'initCreatePage');

const initFlashcardsVocabulairePage = lazyInitializer(
    () => import('../../chinois/pages/flashcards-vocabulaire.js'),
    'initFlashcardsVocabulairePage'
);

const initFlashcardsGrammairePage = lazyInitializer(
    () => import('../../chinois/pages/flashcards-grammaire.js'),
    'initFlashcardsGrammairePage'
);

const initToggleGrammaireMaitrise = lazyInitializer(
    () => import('../../chinois/actions/toggle-grammar-mastery.js'),
    'initToggleGrammaireMaitrise',
    '.grammar-ajax'
);

const initToggleVocabulaireMaitrise = lazyInitializer(
    () => import('../../chinois/actions/toggle-vocabulary-mastery.js'),
    'initToggleVocabulaireMaitrise',
    '.vocabulary-ajax'
);

const initDeleteGrammaire = lazyInitializer(
    () => import('../../chinois/actions/delete-grammar.js'),
    'initDeleteGrammaire',
    '.grammaire-delete'
);

const initDeleteVocabulaire = lazyInitializer(
    () => import('../../chinois/actions/delete-vocabulary.js'),
    'initDeleteVocabulaire',
    '.vocabulaire-delete'
);

// =================================================
// PROFIL INITIALISATIONS
// =================================================

const initProfileCustomization = lazyInitializer(
    () => import('../../profile/pages/customization.js'),
    'initProfileCustomization'
);

// =================================================
// SQL INITIALISATIONS
// =================================================

const initSqlPage = lazyInitializer(() => import('../../sql/pages/sql.js'), 'initSqlPage');

// =================================================
// EXPORT
// =================================================

export const ROUTE_INITIALIZERS = [
    // --------------------------------------------------------------------------
    // MANGA
    // --------------------------------------------------------------------------

    {
        match: /^\/manga(?:\/|$)/,

        initializers: [
            ['AcquireRelease', initAcquireRelease],
            ['UpdateNote', initUpdateNote],
            ['DeleteManga', initDeleteManga],
            ['DeleteArtbook', initDeleteArtbook],
            ['UpdateReadStatus', initUpdateReadStatus]
        ]
    },

    {
        match: /^\/manga\/ajouter\/(manga|artbook)\/?$/,

        initializers: [[ 'AjouterMangaPage', initCreateMangaPage ]]
    },

    {
        match: /^\/manga\/series\/.+\/modifier\/\d+\/?$/,

        initializers: [[ 'ModifierMangaPage', initEditMangaPage ]]
    },

    // --------------------------------------------------------------------------
    // FIGURINE
    // --------------------------------------------------------------------------

    {
        match: /^\/figurine(?:\/|$)/,

        initializers: [
            ['DeleteFigurine', initDeleteFigurine],
            ['UpdateFigurineCollectStatus', initUpdateFigurineCollectStatus]
        ]
    },

    {
        match: /^\/figurine\/ajouter\/?$/,

        initializers: [[ 'AjouterFigurinePage', initCreateFigurinePage ]]
    },

    // --------------------------------------------------------------------------
    // PELUCHE
    // --------------------------------------------------------------------------

    {
        match: /^\/peluche(?:\/|$)/,

        initializers:
        [[ 'DeletePeluche', initDeletePeluche ], [ 'UpdatePelucheCollectStatus', initUpdatePelucheCollectStatus ]]
    },

    {
        match: /^\/peluche\/ajouter\/?$/,

        initializers: [[ 'AjouterPeluchePage', initCreatePeluchePage ]]
    },

    // --------------------------------------------------------------------------
    // NENDOROID
    // --------------------------------------------------------------------------

    {
        match: /^\/nendoroid(?:\/|$)/,

        initializers: [
            ['DeleteNendoroid', initDeleteNendoroid],
            ['UpdateNendoroidCollectStatus', initUpdateNendoroidCollectStatus]
        ]
    },

    {
        match: /^\/nendoroid\/ajouter\/?$/,

        initializers: [[ 'AjouterNendoroidPage', initCreateNendoroidPage ]]
    },

    // --------------------------------------------------------------------------
    // CHINOIS
    // --------------------------------------------------------------------------

    {
        match: /^\/chinois(?:\/|$)/,

        initializers: [
            ['ToggleGrammaireMaitrise', initToggleGrammaireMaitrise],
            ['ToggleVocabulaireMaitrise', initToggleVocabulaireMaitrise],
            ['DeleteGrammaire', initDeleteGrammaire],
            ['DeleteVocabulaire', initDeleteVocabulaire]
        ]
    },

    {
        match: /^\/chinois\/ajouter\/(grammaire|vocabulaire)\/?$/,

        initializers: [[ 'AjouterChinoisPage', initCreateChinoisPage ]]
    },

    {
        match: /^\/chinois\/flashcards\/vocabulaire\/?$/,

        initializers: [[ 'FlashcardsVocabulaire', initFlashcardsVocabulairePage ]]
    },

    {
        match: /^\/chinois\/flashcards\/grammaire\/?$/,

        initializers: [[ 'FlashcardsGrammaire', initFlashcardsGrammairePage ]]
    },

    // --------------------------------------------------------------------------
    // PROFIL
    // --------------------------------------------------------------------------

    {
        match: /^\/profil\/personnalisation\/?$/,

        initializers: [[ 'ProfileCustomization', initProfileCustomization ]]
    },

    // --------------------------------------------------------------------------
    // SQL
    // --------------------------------------------------------------------------

    {
        match: /^\/sql\/?$/,

        initializers: [[ 'SqlPage', initSqlPage ]]
    }
];
