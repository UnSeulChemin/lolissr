// =========================================
// CHINOIS CACHE
// =========================================

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// GRAMMAR
// =========================================

export function invalidateGrammarPages()
{
    invalidatePage('/lolissr/');

    invalidatePage('/lolissr/profil');

    invalidatePage('/lolissr/chinois/grammaire/hsk1');
    invalidatePage('/lolissr/chinois/grammaire/hsk2');
    invalidatePage('/lolissr/chinois/grammaire/hsk3');
    invalidatePage('/lolissr/chinois/grammaire/hsk4');

    invalidatePage('/lolissr/chinois/flashcards/grammaire');
}

// =========================================
// VOCABULARY
// =========================================

export function invalidateVocabularyPages()
{
    invalidatePage('/lolissr/');

    invalidatePage('/lolissr/profil');

    invalidatePage('/lolissr/chinois/vocabulaire/mandarin');
    invalidatePage('/lolissr/chinois/vocabulaire/jinyu');

    invalidatePage('/lolissr/chinois/flashcards/vocabulaire');
}