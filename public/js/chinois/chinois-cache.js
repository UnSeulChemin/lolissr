// =========================================
// CHINOIS CACHE
// =========================================

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// CONFIG
// =========================================

const BASE =
    '/lolissr/chinois';

// =========================================
// GRAMMAR
// =========================================

export function invalidateGrammarPages()
{
    invalidatePage(
        '/lolissr/',
    );

    invalidatePage(
        '/lolissr/profil',
    );

    invalidatePage(
        `${BASE}/grammaire`,
    );
}

// =========================================
// VOCABULARY
// =========================================

export function invalidateVocabularyPages()
{
    invalidatePage(
        '/lolissr/',
    );

    invalidatePage(
        '/lolissr/profil',
    );

    invalidatePage(
        `${BASE}/vocabulaire`,
    );
}