// =========================================
// CHINOIS CACHE
// =========================================

import {
    invalidatePage,
} from '../router/page-invalidation.js';

const BASE = '/lolissr/chinois';

export function invalidateGrammarPages()
{
    invalidatePage(
        `${BASE}/grammaire`,
    );
}

export function invalidateVocabularyPages()
{
    invalidatePage(
        `${BASE}/vocabulaire`,
    );
}
