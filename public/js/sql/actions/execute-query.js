import {
    post,
} from '../../core/http.js';

import {
    delegate,
} from '../../core/dom.js';

import {
    debug,
} from '../../core/debug/debug.js';

import {
    FrontendError,
} from '../../core/errors/FrontendError.js';

import {
    deleteModal,
} from '../../core/modal/modal.js';

// =========================================
// STATE
// =========================================

let initialized =
    false;

// =========================================
// SECURITY
// =========================================

function escapeHtml(
    value,
)
{
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

// =========================================
// ERROR RENDER
// =========================================

function renderError(
    sql,
    message,
)
{
    const container =
        document.getElementById(
            'sql-results',
        );

    if (!container)
    {
        return;
    }

    container.innerHTML =
    `
    <section
        class="
            home-grid
            home-grid-top
            card-grid-3
            sql-grid
        "
    >

        <article
            class="
                card
                transition-card
                card-medium
                sql-query-card
            "
        >

            <h2 class="home-card-title">
                📝 Requête SQL
            </h2>

            <pre class="sql-result-query">${escapeHtml(sql)}</pre>

        </article>

        <article
            class="
                card
                transition-card
                card-link-wide
                card-wide
                sql-result-card
            "
        >

            <h2 class="home-card-title">
                ❌ Erreur
            </h2>

            <p class="sql-error">
                ${escapeHtml(message)}
            </p>

        </article>

    </section>
    `;
}

// =========================================
// RESULT RENDER
// =========================================

function renderResult(
    sql,
    result,
)
{
    const container =
        document.getElementById(
            'sql-results',
        );

    if (!container)
    {
        return;
    }

    if (result.length === 0)
    {
        container.innerHTML =
        `
        <section
            class="
                home-grid
                home-grid-top
                card-grid-3
                sql-grid
            "
        >

            <article
                class="
                    card
                    transition-card
                    card-medium
                    sql-query-card
                "
            >

                <h2 class="home-card-title">
                    📝 Requête SQL
                </h2>

                <pre class="sql-result-query">${escapeHtml(sql)}</pre>

            </article>

            <article
                class="
                    card
                    transition-card
                    card-link-wide
                    card-wide
                    sql-result-card
                "
            >

                <h2 class="home-card-title">
                    📊 Résultat
                </h2>

                <p class="sql-result-count">
                    Aucune ligne retournée.
                </p>

            </article>

        </section>
        `;

        return;
    }

    const columns =
        Object.keys(
            result[0],
        );

    const header =
        columns
            .map(
                column =>
                    `<th>${escapeHtml(column)}</th>`,
            )
            .join('');

    const rows =
        result
            .map(
                row =>
                `
                <tr>
                    ${
                        columns
                            .map(
                                column =>
                                    `<td>${escapeHtml(row[column] ?? '')}</td>`,
                            )
                            .join('')
                    }
                </tr>
                `,
            )
            .join('');

    container.innerHTML =
    `
    <section
        class="
            home-grid
            home-grid-top
            card-grid-3
            sql-grid
        "
    >

        <article
            class="
                card
                transition-card
                card-medium
                sql-query-card
            "
        >

            <h2 class="home-card-title">
                📝 Requête SQL
            </h2>

            <pre class="sql-result-query">${escapeHtml(sql)}</pre>

        </article>

        <article
            class="
                card
                transition-card
                card-link-wide
                card-wide
                sql-result-card
            "
        >

            <h2 class="home-card-title">
                📊 Résultat
            </h2>

            <p class="sql-result-count">
                ${result.length} ligne(s)
            </p>

            <div class="sql-table-wrapper">

                <table class="sql-table">

                    <thead>
                        <tr>
                            ${header}
                        </tr>
                    </thead>

                    <tbody>
                        ${rows}
                    </tbody>

                </table>

            </div>

        </article>

    </section>
    `;
}

// =========================================
// EXECUTE
// =========================================

async function executeQuery(
    form,
)
{
    let sql = '';

    try {

        if (
            form.dataset.loading
            === '1'
        ) {
            return;
        }

        const url =
            form.dataset.url;

        if (!url)
        {
            throw new FrontendError(
                'URL SQL manquante.',
            );
        }

        const textarea =
            form.querySelector(
                '#sql',
            );

        if (
            !(
                textarea
                instanceof HTMLTextAreaElement
            )
        ) {
            throw new FrontendError(
                'Champ SQL introuvable.',
            );
        }

        sql =
            textarea.value.trim();

        if (sql === '')
        {
            throw new FrontendError(
                'Veuillez saisir une requête SQL.',
            );
        }

        const isDangerousQuery =
            /^\s*(INSERT|UPDATE|DELETE|DROP|TRUNCATE|ALTER|CREATE|RENAME|REPLACE)\b/i
                .test(
                    sql,
                );

        if (
            isDangerousQuery
        )
        {
            const confirmed =
                await deleteModal(
                    'Cette requête peut modifier la base de données. Continuer ?',
                );

            if (!confirmed)
            {
                return;
            }
        }

        form.dataset.loading =
            '1';

        const data =
            await post(
                url,
                {
                    sql,
                },
            );

        if (
            data?.success
            !== true
        ) {
            throw new FrontendError(
                data?.message
                || 'Erreur SQL.',
            );
        }

        const result =
            Array.isArray(
                data.data?.result,
            )
                ? data.data.result
                : [];

        renderResult(
            sql,
            result,
        );

        debug(
            'SQL',
            'success',
            {
                rows:
                    result.length,
            },
        );

    } catch (error) {

        renderError(
            sql,
            error instanceof Error
                ? error.message
                : 'Erreur inconnue',
        );

    } finally {

        delete form.dataset.loading;
    }
}

// =========================================
// INIT
// =========================================

export function initExecuteQuery()
{
    if (initialized)
    {
        return;
    }

    initialized =
        true;

    delegate(
        document,
        'submit',
        '[data-sql-form]',
        (
            event,
            form,
        ) =>
        {
            if (
                !(
                    form
                    instanceof HTMLFormElement
                )
            ) {
                return;
            }

            event.preventDefault();

            void executeQuery(
                form,
            );
        },
    );

    debug(
        'SQL',
        'initialized',
    );
}