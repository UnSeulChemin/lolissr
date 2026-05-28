<?php

declare(strict_types=1);

final class HtmlReport
{
    public static function generate(
        array $results,
        Stats $stats,
        string $file
    ): void {

        $rows = '';

        foreach ($results as $result)
        {
            $status = $result['status'];
            $label = $result['label'];
            $path = $result['path'];
            $duration = number_format(
                $result['duration'] * 1000,
                2
            );

            $rows .= '
            <tr>
                <td>' . htmlspecialchars($status) . '</td>
                <td>' . htmlspecialchars($label) . '</td>
                <td>' . htmlspecialchars($path) . '</td>
                <td>' . $duration . ' ms</td>
            </tr>';
        }

        $html = '
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>LoliSSR Report</title>

<style>

body{
    font-family:Segoe UI,sans-serif;
    margin:40px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,
td{
    border:1px solid #ddd;
    padding:10px;
}

th{
    background:#f5f5f5;
}

.ok{
    color:green;
    font-weight:bold;
}

.fail{
    color:red;
    font-weight:bold;
}

</style>

</head>

<body>

<h1>LoliSSR HTTP Report</h1>

<p>Total : ' . $stats->total() . '</p>
<p>OK : ' . $stats->successCount() . '</p>
<p>FAIL : ' . $stats->failCount() . '</p>

<table>

<thead>
<tr>
    <th>Status</th>
    <th>Label</th>
    <th>Path</th>
    <th>Temps</th>
</tr>
</thead>

<tbody>
' . $rows . '
</tbody>

</table>

</body>
</html>';

        file_put_contents(
            $file,
            $html
        );
    }
}