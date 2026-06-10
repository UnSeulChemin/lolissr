// =========================================
// COPY INIT
// =========================================

import {
    delegate,
} from '../dom.js';

import {
    copyText,
} from './copy.js';

// =========================================
// INIT
// =========================================

let initialized =
    false;

export function initCopy()
{
    if (initialized) {

        return;
    }

    initialized =
        true;

    delegate(
        document,
        'click',
        '[data-copy]',
        async (
            _,
            element,
        ) =>
        {
            const text =
                element.dataset.copy;

            await copyText(
                text,
            );
        },
    );
}