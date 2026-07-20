// =========================================
// CHINOIS CACHE
// =========================================

import {
    appUrl,
} from '../core/url.js';

import {
    invalidatePage,
} from '../router/page-invalidation.js';

// =========================================
// GRAMMAR
// =========================================

export function invalidateGrammarPages()
{
    invalidatePage(appUrl());

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('chinois/grammaire/hsk1'));

    invalidatePage(appUrl('chinois/grammaire/hsk2'));

    invalidatePage(appUrl('chinois/grammaire/hsk3'));

    invalidatePage(appUrl('chinois/grammaire/hsk4'));

    invalidatePage(appUrl('chinois/flashcards/grammaire'));
}

// =========================================
// VOCABULARY
// =========================================

export function invalidateVocabularyPages()
{
    invalidatePage(appUrl());

    invalidatePage(appUrl('profil'));

    invalidatePage(appUrl('chinois/vocabulaire/mandarin'));

    invalidatePage(appUrl('chinois/vocabulaire/jinyu'));

    invalidatePage(appUrl('chinois/flashcards/vocabulaire'));
}