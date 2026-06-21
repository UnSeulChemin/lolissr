import {
    invalidatePage,
} from './page-invalidation.js';

export function refreshCurrentPage()
{
    invalidatePage(
        window.location.href,
    );
}