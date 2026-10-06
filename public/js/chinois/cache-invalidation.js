// =================================================
// CACHE DU CHINOIS
// =================================================

import { appUrl } from '../core/url.js';

import { invalidatePages } from '../router/pages/invalidation.js';

// =================================================
// GRAMMAIRE
// =================================================

export function invalidateGrammarPages()
{
    invalidatePages([
        [appUrl(), {descendants: false}],
        [appUrl('profil')],
        [appUrl('chinois/grammaire/hsk1')],
        [appUrl('chinois/grammaire/hsk2')],
        [appUrl('chinois/grammaire/hsk3')],
        [appUrl('chinois/grammaire/hsk4')],
        [appUrl('chinois/flashcards/grammaire')]
    ]);
}

// =================================================
// VOCABULAIRE
// =================================================

export function invalidateVocabularyPages()
{
    invalidatePages([
        [appUrl(), {descendants: false}],
        [appUrl('profil')],
        [appUrl('chinois/vocabulaire/mandarin')],
        [appUrl('chinois/vocabulaire/jinyu')],
        [appUrl('chinois/flashcards/vocabulaire')]
    ]);
}