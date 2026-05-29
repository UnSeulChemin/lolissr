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
                (string) $result['status'];

            $label =
                htmlspecialchars(
                    (string) $result['label'],
                );

            $path =
                htmlspecialchars(
                    (string) $result['path'],
                );

            $duration =
                number_format(
                    ((float) $result['duration']) * 1000,
                    2,
                );

            $badgeClass =
                $status === 'OK'
                    ? 'badge-ok'
                    : 'badge-fail';

            $rows .=
                '<tr>'
                . '<td><span class="' . $badgeClass . '">' . $status . '</span></td>'
                . '<td>' . $label . '</td>'
                . '<td>' . $path . '</td>'
                . '<td>' . $duration . ' ms</td>'
                . '</tr>';
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

<title>LoliSSR Report</title>

<style>

:root{

    --background:#ebebeb;

    --surface:#ffffff;
    --surface-soft:#f8f8fb;

    --violet:#7b2cff;

    --violet-soft:
        rgba(123,44,255,.08);

    --violet-border:
        rgba(123,44,255,.12);

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

    font-family:
        Montserrat,
        Segoe UI,
        sans-serif;
}

.container{

    width:min(
        1400px,
        100%
    );

    margin:auto;
}

.hero{

    position:relative;

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

    border:
        1px solid
        rgba(123,44,255,.10);

    box-shadow:
        0 12px 28px rgba(0,0,0,.07);
}

.hero h1{

    margin:0;

    font-size:3rem;

    color:#7b2cff;
}

.hero p{

    opacity:.75;
}

.grid{

    display:grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(
                250px,
                1fr
            )
        );

    gap:24px;

    margin-bottom:40px;
}

.card{

    padding:24px;

    border-radius:24px;

    background:
        linear-gradient(
            180deg,
            #ffffff,
            #f8f8fb
        );

    border:
        1px solid
        rgba(123,44,255,.10);

    box-shadow:
        var(--shadow);

    text-align:center;
}

.card-title{

    font-size:1rem;

    opacity:.65;

    margin-bottom:12px;
}

.card-value{

    font-size:3rem;

    font-weight:800;
}

.card-value.violet{

    color:#7b2cff;
}

.table-card{

    overflow:hidden;

    border-radius:24px;

    background:#fff;

    border:
        1px solid
        rgba(123,44,255,.10);

    box-shadow:
        var(--shadow);
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

    border-top:
        1px solid
        rgba(123,44,255,.06);
}

tr:hover{

    background:
        rgba(123,44,255,.04);
}

.badge-ok{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    min-width:90px;

    padding:8px 14px;

    border-radius:999px;

    color:white;

    font-weight:700;

    background:#27d99a;
}

.badge-fail{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    min-width:90px;

    padding:8px 14px;

    border-radius:999px;

    color:white;

    font-weight:700;

    background:
        linear-gradient(
            180deg,
            #ff6d6d,
            #ff4d4d
        );

    box-shadow:
        0 0 20px
        rgba(255,80,80,.25);
}

.footer{

    margin-top:30px;

    text-align:center;

    opacity:.5;

    font-size:.9rem;
}

</style>

</head>

<body>

<div class="container">

    <div class="hero">

        <h1>📜 LoliSSR Report</h1>

        <p>
            Rapport généré automatiquement par la suite HTTP Safe
        </p>

    </div>

    <div class="grid">

        <div class="card">
            <div class="card-title">Tests</div>
            <div class="card-value">
                {$stats->total()}
            </div>
        </div>

        <div class="card">
            <div class="card-title">Succès</div>
            <div class="card-value">
                {$stats->successCount()}
            </div>
        </div>

        <div class="card">
            <div class="card-title">Échecs</div>
            <div class="card-value">
                {$stats->failCount()}
            </div>
        </div>

        <div class="card">
            <div class="card-title">Success Rate</div>
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

</body>
</html>
HTML;

        file_put_contents(
            $file,
            $html,
        );
    }
}