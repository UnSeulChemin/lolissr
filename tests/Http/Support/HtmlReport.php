<?php

declare(strict_types=1);

use RuntimeException;

final class HtmlReport
{
    private const FILE_TITLE = 'LoliSSR HTTP Report';

    private function __construct()
    {
    }

    // =========================================
    // GÉNÉRATION
    // =========================================

    /**
     * @param array<int, array<string, mixed>> $results
     */
    public static function generate(array $results, Stats $stats, string $file): void
    {
        $html = self::render(
            rows: self::renderRows($results),
            total: $stats->total(),
            success: $stats->successCount(),
            fail: $stats->failCount(),
            successRate: $stats->successRate(),
            generatedAt: date('d/m/Y H:i:s')
        );

        if (file_put_contents($file, $html, LOCK_EX) === false)
        {
            throw new RuntimeException(
                "Impossible de générer le rapport HTTP : {$file}"
            );
        }
    }

    // =========================================
    // RÉSULTATS
    // =========================================

    /**
     * @param array<int, array<string, mixed>> $results
     */
    private static function renderRows(array $results): string
    {
        $rows = '';

        foreach ($results as $result)
        {
            $status = (string) ($result['status'] ?? 'FAIL');
            $label = self::escape($result['label'] ?? '');
            $method = self::escape($result['method'] ?? '');
            $path = self::escape($result['path'] ?? '');
            $reason = self::escape($result['reason'] ?? '');
            $expectedStatus = self::escape($result['expected_status'] ?? '');
            $actualStatus = self::escape($result['http_status'] ?? '');
            $headers = self::escape($result['headers'] ?? '');
            $body = self::escape($result['body'] ?? '');
            $duration = number_format(((float) ($result['duration'] ?? 0.0)) * 1000, 2);

            $badgeClass = $status === 'OK'
                ? 'badge-ok'
                : 'badge-fail';

            $rows .= self::renderMainRow(
                status: $status,
                badgeClass: $badgeClass,
                label: $label,
                method: $method,
                path: $path,
                duration: $duration,
                reason: $reason
            );

            if ($status === 'FAIL')
            {
                $rows .= self::renderDebugRow(
                    expectedStatus: $expectedStatus,
                    actualStatus: $actualStatus,
                    reason: $reason,
                    headers: $headers,
                    body: $body
                );
            }
        }

        return $rows;
    }

    private static function renderMainRow(
        string $status,
        string $badgeClass,
        string $label,
        string $method,
        string $path,
        string $duration,
        string $reason
    ): string {
        return <<<HTML
<tr class="main-row" data-status="{$status}">
    <td><span class="{$badgeClass}">{$status}</span></td>
    <td>{$method} — {$label}</td>
    <td>{$path}</td>
    <td>{$duration} ms</td>
    <td>{$reason}</td>
</tr>
HTML;
    }

    private static function renderDebugRow(
        string $expectedStatus,
        string $actualStatus,
        string $reason,
        string $headers,
        string $body
    ): string {
        return <<<HTML
<tr class="debug-row" data-status="FAIL">
    <td colspan="5">
        <div class="debug-content">
            <h3>Debug</h3>

            <p>
                <strong>Expected :</strong>
                {$expectedStatus}
            </p>

            <p>
                <strong>Actual :</strong>
                {$actualStatus}
            </p>

            <p>
                <strong>Reason :</strong>
                {$reason}
            </p>

            <h4>Headers</h4>
            <pre>{$headers}</pre>

            <h4>Response</h4>
            <pre>{$body}</pre>
        </div>
    </td>
</tr>
HTML;
    }

    // =========================================
    // DOCUMENT
    // =========================================

    private static function render(
        string $rows,
        int $total,
        int $success,
        int $fail,
        float $successRate,
        string $generatedAt
    ): string {
        $title = self::FILE_TITLE;

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{$title}</title>

    <style>

        :root
        {
            --background: #ebebeb;
            --surface: #ffffff;
            --surface-soft: #f8f8fb;
            --violet: #7b2cff;
            --text: #1a1a1a;
            --success: #27d99a;
            --danger: #ff4d4d;
            --shadow:
                0 10px 30px rgba(0, 0, 0, .06),
                0 2px 10px rgba(0, 0, 0, .03);
        }

        *
        {
            box-sizing: border-box;
        }

        body
        {
            margin: 0;
            padding: 50px;
            background: var(--background);
            color: var(--text);
            font-family: Montserrat, "Segoe UI", sans-serif;
        }

        .container
        {
            width: min(1400px, 100%);
            margin: auto;
        }

        .hero
        {
            margin-bottom: 40px;
            padding: 34px 30px;
            text-align: center;
            border: 1px solid rgba(123, 44, 255, .10);
            border-radius: 28px;
            background: linear-gradient(
                180deg,
                rgba(255, 255, 255, .98),
                rgba(123, 44, 255, .06)
            );
            box-shadow: 0 12px 28px rgba(0, 0, 0, .07);
        }

        .hero h1
        {
            margin: 0;
            color: var(--violet);
            font-size: 3rem;
        }

        .hero p
        {
            opacity: .75;
        }

        .grid
        {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .card
        {
            padding: 24px;
            border: 1px solid rgba(123, 44, 255, .10);
            border-radius: 24px;
            text-align: center;
            background: linear-gradient(180deg, var(--surface), var(--surface-soft));
            box-shadow: var(--shadow);
        }

        .clickable
        {
            cursor: pointer;
            transition: transform .15s ease;
        }

        .clickable:hover
        {
            transform: translateY(-3px);
        }

        .card-title
        {
            margin-bottom: 12px;
            opacity: .65;
        }

        .card-value
        {
            font-size: 3rem;
            font-weight: 800;
        }

        .violet
        {
            color: var(--violet);
        }

        .fail-count
        {
            color: var(--danger);
        }

        .table-card
        {
            overflow: hidden;
            border: 1px solid rgba(123, 44, 255, .10);
            border-radius: 24px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        table
        {
            width: 100%;
            border-collapse: collapse;
        }

        th
        {
            padding: 18px;
            text-align: left;
            color: #5e4b75;
            background: linear-gradient(
                135deg,
                rgba(123, 44, 255, .14),
                rgba(178, 76, 255, .08)
            );
        }

        td
        {
            padding: 18px;
            border-top: 1px solid rgba(123, 44, 255, .06);
        }

        tr:hover
        {
            background: rgba(123, 44, 255, .04);
        }

        .main-row
        {
            cursor: pointer;
        }

        .debug-row
        {
            display: none;
        }

        .debug-row.open
        {
            display: table-row;
        }

        .debug-content
        {
            padding: 20px;
            background: rgba(123, 44, 255, .03);
        }

        .debug-content pre
        {
            overflow: auto;
            padding: 12px;
            border-radius: 12px;
            background: #f4f4f4;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .badge-ok,
        .badge-fail
        {
            display: inline-flex;
            justify-content: center;
            min-width: 90px;
            padding: 8px 14px;
            border-radius: 999px;
            color: #ffffff;
            font-weight: 700;
        }

        .badge-ok
        {
            background: var(--success);
        }

        .badge-fail
        {
            background: linear-gradient(180deg, #ff6d6d, var(--danger));
        }

        .footer
        {
            margin-top: 30px;
            text-align: center;
            opacity: .5;
        }

        @media (max-width: 800px)
        {
            body
            {
                padding: 20px;
            }

            .hero h1
            {
                font-size: 2rem;
            }

            .table-card
            {
                overflow-x: auto;
            }

            table
            {
                min-width: 900px;
            }
        }

    </style>

</head>

<body>

    <div class="container">

        <div class="hero">

            <h1>📜 LoliSSR Report</h1>

            <p>
                Rapport HTTP généré automatiquement
            </p>

        </div>

        <div class="grid">

            <button
                class="card clickable"
                type="button"
                data-filter="ALL"
            >

                <span class="card-title">
                    Tests
                </span>

                <span class="card-value">
                    {$total}
                </span>

            </button>

            <button
                class="card clickable"
                type="button"
                data-filter="OK"
            >

                <span class="card-title">
                    Succès
                </span>

                <span class="card-value">
                    {$success}
                </span>

            </button>

            <button
                class="card clickable"
                type="button"
                data-filter="FAIL"
            >

                <span class="card-title">
                    Échecs
                </span>

                <span class="card-value fail-count">
                    {$fail}
                </span>

            </button>

            <div class="card">

                <div class="card-title">
                    Taux de réussite
                </div>

                <div class="card-value violet">
                    {$successRate}%
                </div>

            </div>

        </div>

        <div class="table-card">

            <table>

                <thead>

                    <tr>
                        <th>Statut</th>
                        <th>Test</th>
                        <th>Route</th>
                        <th>Temps</th>
                        <th>Raison</th>
                    </tr>

                </thead>

                <tbody>
                    {$rows}
                </tbody>

            </table>

        </div>

        <div class="footer">
            Généré le {$generatedAt}
        </div>

    </div>

    <script>

        function closeDebugRows()
        {
            document
                .querySelectorAll('.debug-row.open')
                .forEach((row) => row.classList.remove('open'));
        }

        function filterRows(status)
        {
            closeDebugRows();

            document
                .querySelectorAll('.main-row')
                .forEach((row) =>
                {
                    const visible =
                        status === 'ALL'
                        || row.dataset.status === status;

                    row.hidden = ! visible;

                    const debugRow = row.nextElementSibling;

                    if (
                        debugRow
                        && debugRow.classList.contains('debug-row')
                    ) {
                        debugRow.hidden = ! visible;
                    }
                });
        }

        document
            .querySelectorAll('[data-filter]')
            .forEach((button) =>
            {
                button.addEventListener(
                    'click',
                    () => filterRows(button.dataset.filter ?? 'ALL')
                );
            });

        document
            .querySelectorAll('.main-row')
            .forEach((row) =>
            {
                row.addEventListener(
                    'click',
                    () =>
                    {
                        const debugRow = row.nextElementSibling;

                        if (
                            ! debugRow
                            || ! debugRow.classList.contains('debug-row')
                        ) {
                            return;
                        }

                        debugRow.classList.toggle('open');
                    }
                );
            });

    </script>

</body>

</html>
HTML;
    }

    // =========================================
    // ÉCHAPPEMENT
    // =========================================

    private static function escape(mixed $value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}