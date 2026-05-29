<?php

declare(strict_types=1);

final class HtmlReport
{
    public static function generate(
        array $results,
        Stats $stats,
        string $file,
    ): void {

        $rows = '';

        foreach ($results as $result)
        {
            $status =
                (string) ($result['status'] ?? 'FAIL');

            $label =
                htmlspecialchars(
                    (string) ($result['label'] ?? ''),
                );

            $path =
                htmlspecialchars(
                    (string) ($result['path'] ?? ''),
                );

            $reason =
                htmlspecialchars(
                    (string) ($result['reason'] ?? ''),
                );

            $expectedStatus =
                htmlspecialchars(
                    (string) ($result['expected_status'] ?? ''),
                );

            $actualStatus =
                htmlspecialchars(
                    (string) ($result['http_status'] ?? ''),
                );

            $headers =
                htmlspecialchars(
                    (string) ($result['headers'] ?? ''),
                );

            $body =
                htmlspecialchars(
                    (string) ($result['body'] ?? ''),
                );

            $duration =
                number_format(
                    ((float) ($result['duration'] ?? 0)) * 1000,
                    2,
                );

            $badgeClass =
                $status === 'OK'
                    ? 'badge-ok'
                    : 'badge-fail';

            $rows .=

                '<tr class="main-row" data-status="' . $status . '">'

                    . '<td><span class="' . $badgeClass . '">' . $status . '</span></td>'

                    . '<td>' . $label . '</td>'

                    . '<td>' . $path . '</td>'

                    . '<td>' . $duration . ' ms</td>'

                    . '<td>' . $reason . '</td>'

                . '</tr>';

            if ($status === 'FAIL')
            {
                $rows .=

                    '<tr class="debug-row" data-status="FAIL">'

                        . '<td colspan="5">'

                            . '<div class="debug-content">'

                                . '<h3>Debug</h3>'

                                . '<p><strong>Expected :</strong> '
                                . $expectedStatus
                                . '</p>'

                                . '<p><strong>Actual :</strong> '
                                . $actualStatus
                                . '</p>'

                                . '<p><strong>Reason :</strong> '
                                . $reason
                                . '</p>'

                                . '<h4>Headers</h4>'

                                . '<pre>'
                                . $headers
                                . '</pre>'

                                . '<h4>Response</h4>'

                                . '<pre>'
                                . $body
                                . '</pre>'

                            . '</div>'

                        . '</td>'

                    . '</tr>';
            }


        }

        $successRate =
            $stats->successRate();

        $generatedAt =
            date('d/m/Y H:i:s');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>LoliSSR HTTP Report</title>

<style>

:root{
    --background:#ebebeb;
    --surface:#ffffff;
    --surface-soft:#f8f8fb;
    --violet:#7b2cff;
    --text:#1a1a1a;

    --shadow:
        0 10px 30px rgba(0,0,0,.06),
        0 2px 10px rgba(0,0,0,.03);
}

*{
    box-sizing:border-box;
}

body{
    margin:0;
    padding:50px;
    background:var(--background);
    color:var(--text);
    font-family:Montserrat,Segoe UI,sans-serif;
}

.container{
    width:min(1400px,100%);
    margin:auto;
}

.hero{
    margin-bottom:40px;
    padding:34px 30px;
    text-align:center;
    border-radius:28px;

    background:
        linear-gradient(
            180deg,
            rgba(255,255,255,.98),
            rgba(123,44,255,.06)
        );

    border:1px solid rgba(123,44,255,.10);

    box-shadow:
        0 12px 28px rgba(0,0,0,.07);
}

.hero h1{
    margin:0;
    color:#7b2cff;
    font-size:3rem;
}

.hero p{
    opacity:.75;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:24px;
    margin-bottom:24px;
}

.card{
    padding:24px;
    border-radius:24px;
    text-align:center;
    background:linear-gradient(180deg,#fff,#f8f8fb);
    border:1px solid rgba(123,44,255,.10);
    box-shadow:var(--shadow);
}

.clickable{
    cursor:pointer;
    transition:.15s ease;
}

.clickable:hover{
    transform:translateY(-3px);
}

.card-title{
    opacity:.65;
    margin-bottom:12px;
}

.card-value{
    font-size:3rem;
    font-weight:800;
}

.violet{
    color:#7b2cff;
}

.fail-count{
    color:#ff4d4d;
}

.filters{
    display:flex;
    gap:12px;
    margin-bottom:20px;
}

.filters button{
    border:none;
    border-radius:14px;
    padding:12px 18px;
    cursor:pointer;
    font-weight:700;
    color:#fff;
    background:#7b2cff;
}

.table-card{
    overflow:hidden;
    border-radius:24px;
    background:#fff;
    border:1px solid rgba(123,44,255,.10);
    box-shadow:var(--shadow);
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    padding:18px;
    text-align:left;

    color:#5e4b75;

    background:
        linear-gradient(
            135deg,
            rgba(123,44,255,.14),
            rgba(178,76,255,.08)
        );
}

td{
    padding:18px;
    border-top:1px solid rgba(123,44,255,.06);
}

tr:hover{
    background:rgba(123,44,255,.04);
}

.main-row{
    cursor:pointer;
}

.debug-row{
    display:none;
}

.debug-row.open{
    display:table-row;
}

.debug-content{

    padding:20px;

    background:
        rgba(
            123,
            44,
            255,
            .03
        );
}

.debug-content pre{

    overflow:auto;

    padding:12px;

    border-radius:12px;

    background:#f4f4f4;

    white-space:pre-wrap;
}

.badge-ok{
    display:inline-flex;
    justify-content:center;
    min-width:90px;
    padding:8px 14px;
    border-radius:999px;
    color:#fff;
    font-weight:700;
    background:#27d99a;
}

.badge-fail{
    display:inline-flex;
    justify-content:center;
    min-width:90px;
    padding:8px 14px;
    border-radius:999px;
    color:#fff;
    font-weight:700;
    background:linear-gradient(180deg,#ff6d6d,#ff4d4d);
}

.footer{
    margin-top:30px;
    text-align:center;
    opacity:.5;
}

</style>
</head>

<body>

<div class="container">

    <div class="hero">
        <h1>📜 LoliSSR Report</h1>
        <p>Rapport HTTP généré automatiquement</p>
    </div>

    <div class="grid">

        <div class="card clickable" onclick="showAll()">
            <div class="card-title">Tests</div>
            <div class="card-value">{$stats->total()}</div>
        </div>

        <div class="card clickable" onclick="showSuccess()">
            <div class="card-title">Succès</div>
            <div class="card-value">{$stats->successCount()}</div>
        </div>

        <div class="card clickable" onclick="showFailures()">
            <div class="card-title">Échecs</div>
            <div class="card-value fail-count">{$stats->failCount()}</div>
        </div>

        <div class="card">
            <div class="card-title">Success Rate</div>
            <div class="card-value violet">{$successRate}%</div>
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

function filterRows(status)
{
    document
        .querySelectorAll('tbody tr')
        .forEach(row => {

            if (status === null)
            {
                row.style.display = '';
                return;
            }

            row.style.display =
                row.dataset.status === status
                    ? ''
                    : 'none';
        });
}

function showAll()
{
    filterRows(null);
}

function showSuccess()
{
    filterRows('OK');
}

function showFailures()
{
    filterRows('FAIL');
}

document
    .querySelectorAll('.main-row')
    .forEach((row) => {

        row.addEventListener(
            'click',
            () => {

                const debugRow =
                    row.nextElementSibling;

                if (
                    !debugRow
                    || !debugRow.classList.contains(
                        'debug-row',
                    )
                ) {
                    return;
                }

                debugRow.classList.toggle(
                    'open',
                );
            },
        );
    });

</script>

</body>
</html>
HTML;

        file_put_contents(
            $file,
            $html,
        );
    }
}
