<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/Support/HttpClient.php';

// Test explicite avec deux comptes et contenus jetables sur le serveur local.
// Toutes les fixtures sont supprimees dans finally, meme en cas d'echec.
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', env('DB_HOST'), env_int('DB_PORT'), env('DB_NAME'));
$db = new PDO($dsn, (string) env('DB_USER'), (string) env('DB_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$check = static function (bool $ok, string $message): void
{
    if (! $ok) throw new RuntimeException($message);
};
$accounts = [];
$cookies = [];
$tokens = [];
$wordIds = [];
$suffix = bin2hex(random_bytes(8));
$password = bin2hex(random_bytes(16));
$marker = 'ownership-' . $suffix;
$jsonHeaders = ['Accept: application/json'];
$post = static fn (string $path, array $data): array => http_post(http_base() . $path,
    ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'], http_build_query($data));
$successCount = static function (): int
{
    $response = http_get(http_base() . '/profil/succes');
    if ($response['status'] !== 200) throw new RuntimeException('Achievements page unavailable');
    return substr_count($response['body'], 'success-item is-unlocked');
};
try
{
    $insertUser = $db->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    foreach (['a', 'b'] as $label)
    {
        $name = 'ownership_' . $label . '_' . $suffix;
        $insertUser->execute([$name, password_hash($password, PASSWORD_DEFAULT)]);
        $accounts[$label] = (int) $db->lastInsertId();
        http_set_cookie('');
        $page = http_get(http_base() . '/connexion');
        $loginToken = http_extract_csrf($page['body']);
        $check($page['status'] === 200 && $loginToken !== null, 'Login form unavailable');
        $login = http_post(http_base() . '/connexion', ['Content-Type: application/x-www-form-urlencoded'],
            http_build_query(['username' => $name, 'password' => $password, 'csrf_token' => $loginToken]));
        $check(in_array($login['status'], [302, 303], true), 'Fixture login failed');
        $cookies[$label] = http_cookie();
        $profile = http_get(http_base() . '/profil');
        $check($profile['status'] === 200 && str_contains($profile['body'], $name), 'Session resolved to wrong account');
        $form = http_get(http_base() . '/chinois/ajouter/vocabulaire');
        $tokens[$label] = http_extract_csrf($form['body']);
        $check($tokens[$label] !== null, 'Authenticated CSRF token unavailable');
    }
    $check($cookies['a'] !== $cookies['b'] && $tokens['a'] !== $tokens['b'], 'Sessions/CSRF tokens are shared');
    $insertWord = $db->prepare('INSERT INTO chinois_vocabulaire (user_id, langue, mot, pinyin, type, traduction, exemple) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach (['a', 'b'] as $label)
    {
        $insertWord->execute([$accounts[$label], 'mandarin', $marker . '-' . $label, 'fixture', 'fixture', 'fixture', 'fixture']);
        $wordIds[$label] = (int) $db->lastInsertId();
    }
    // Donner deux contenus a A et un a B rend une fuite du cache mesurable.
    $insertWord->execute([$accounts['a'], 'mandarin', $marker . '-extra', 'fixture', 'fixture', 'fixture', 'fixture']);
    $baselineSuccesses = [];
    foreach (['a', 'b'] as $label)
    {
        http_set_cookie($cookies[$label]);
        $db->prepare('INSERT INTO chinois_grammaire (user_id, niveau, section, categorie, titre, structure, phrase, pinyin, traduction, explication, position, section_position, categorie_position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, 1)')
            ->execute([$accounts[$label], 'HSK1', 'Ancienne', 'Fixture', 'Rule', 'Fixture', 'Fixture', 'Fixture', 'Fixture', 'Fixture']);
        $grammarId = (int) $db->lastInsertId();
        $update = http_post(http_base() . '/chinois/grammaire/hsk1/modifier/' . $grammarId,
            ['Content-Type: application/x-www-form-urlencoded'], http_build_query([
                'csrf_token' => $tokens[$label], 'return_to' => 'chinois/grammaire/hsk1?section=ancienne',
                'niveau' => 'HSK2', 'section' => 'Nouvelle', 'categorie' => 'Fixture', 'titre' => 'Rule',
                'structure' => 'Fixture', 'phrase' => 'Fixture', 'pinyin' => 'Fixture', 'traduction' => 'Fixture', 'explication' => 'Fixture'
            ]));
        $check($update['status'] === 302, 'Grammar move failed');
        $location = '';
        foreach ($update['headers'] as $header)
            if (stripos($header, 'Location: ') === 0) $location = trim(substr($header, 10));
        $check(str_contains($location, '/hsk2?section='), 'Grammar move returned to the old level or section');
        $movedPage = http_get('http://localhost' . $location);
        $check($movedPage['status'] === 200 && str_contains($movedPage['body'], 'Nouvelle'), 'Grammar move redirected to a missing section');
        $db->prepare('DELETE FROM chinois_grammaire WHERE id = ? AND user_id = ?')->execute([$grammarId, $accounts[$label]]);
        $detailPath = '/chinois/vocabulaire/mandarin/recherche/' . $wordIds[$label];
        $detail = http_get(http_base() . $detailPath);
        $check($detail['status'] === 200 && str_contains($detail['body'], $marker . '-' . $label),
            'Own vocabulary detail must render its word in HTML');
        $detail = http_get(http_base() . $detailPath, [...$jsonHeaders, 'X-Page-Format: fragment']);
        $detailPayload = json_decode($detail['body'], true, 512, JSON_THROW_ON_ERROR);
        $check($detail['status'] === 200 && ($detailPayload['type'] ?? null) === 'page'
            && str_contains($detailPayload['page']['html'] ?? '', $marker . '-' . $label),
            'Own vocabulary detail must render its word in SPA navigation');
        $baselineSuccesses[$label] = $successCount();
        $check(http_get(http_base() . '/chinois/vocabulaire/mandarin/page/999')['status'] === 404, 'Invalid vocabulary page accepted');
        $reconciled = http_get(http_base() . '/chinois/vocabulaire/mandarin/page/999?reconcile=1');
        $check($reconciled['status'] === 200 && str_contains($reconciled['body'], 'data-vocabulary-url='), 'Shrinking vocabulary pages were not reconciled');
        $search = http_get(http_base() . '/recherche?q=' . rawurlencode($marker), $jsonHeaders);
        $payload = json_decode($search['body'], true, 512, JSON_THROW_ON_ERROR);
        $check($search['status'] === 200 && count($payload['data']['chinois']) === ($label === 'a' ? 2 : 1), 'Global search leaked foreign contents');
        $home = http_get(http_base() . '/');
        $check($home['status'] === 200 && preg_match('/Total vocabulaires\s*<\/h2>\s*<p[^>]*>\s*(\d+)/u', $home['body'], $total) === 1
            && (int) $total[1] === ($label === 'a' ? 2 : 1), 'Dashboard cache is shared across accounts');
        $check(http_get(http_base() . '/admin/sql')['status'] === 404, 'Removed SQL console is accessible');
        $check(http_get(http_base() . '/admin')['status'] === 404, 'Ordinary account can access administration');
        $check(http_get(http_base() . '/admin/commandes/etat', ['Accept: application/json'])['status'] === 404, 'Ordinary account can read job status and logs');
        $check(http_get(http_base() . '/admin/dev')['status'] === 404, 'Ordinary account can access development tools');
        $check(http_get(http_base() . '/admin/dev/commandes')['status'] === 404, 'Ordinary account can access maintenance');
        foreach (['images', 'cache'] as $maintenanceTask)
            $check($post('/admin/dev/commandes/' . $maintenanceTask, ['csrf_token' => $tokens[$label]])['status'] === 404, 'Ordinary account can trigger maintenance');
        $check(http_get(http_base() . '/admin/commandes')['status'] === 404, 'Ordinary account can access commands');
        foreach (['/admin/commandes/sorties', '/admin/commandes/sorties/mon-compte'] as $releasePath)
            $check($post($releasePath, ['csrf_token' => $tokens[$label]])['status'] === 404, 'Ordinary account can trigger release sync');
        $check($post('/admin/commandes/recommandations', ['csrf_token' => $tokens[$label]])['status'] === 404,
            'Ordinary account can trigger recommendation sync with a valid CSRF token');
        $check($post('/admin/commandes/recommandations/mon-compte', ['csrf_token' => $tokens[$label]])['status'] === 404,
            'Ordinary account can trigger personal recommendation sync');
    }
    http_set_cookie($cookies['b']);
    $check(http_get(http_base() . '/chinois/vocabulaire/mandarin/recherche/' . $wordIds['a'])['status'] === 404, 'Foreign content readable by ID');
    $check($post('/chinois/ajax/toggle-vocabulaire-maitrise', ['id' => $wordIds['a'], 'csrf_token' => $tokens['b']])['status'] === 404,
        'Foreign progress can be changed');
    $check($post('/chinois/ajax/delete-vocabulaire', ['id' => $wordIds['a'], 'csrf_token' => $tokens['b']])['status'] === 404,
        'Foreign content can be deleted');
    $crossCsrf = $post('/chinois/ajax/toggle-vocabulaire-maitrise', ['id' => $wordIds['b'], 'csrf_token' => $tokens['a']]);
    $check($crossCsrf['status'] === 419, 'Unexpected cross-session CSRF status: ' . $crossCsrf['status']);
    http_set_cookie($cookies['a']);
    $result = $post('/chinois/ajax/toggle-vocabulaire-maitrise', ['id' => $wordIds['a'], 'csrf_token' => $tokens['a']]);
    $payload = json_decode($result['body'], true, 512, JSON_THROW_ON_ERROR);
    $check($result['status'] === 200 && $payload['data']['xpEarned'] === true, 'Own mastery did not grant XP');
    $check($successCount() > $baselineSuccesses['a'], 'Own success was not unlocked');
    http_set_cookie($cookies['b']);
    $check($successCount() === $baselineSuccesses['b'], 'Another account inherited unlocked successes');
    $state = $db->prepare('SELECT maitrise, xp_rewarded FROM chinois_vocabulaire WHERE id = ?');
    $state->execute([$wordIds['b']]);
    $check($state->fetch(PDO::FETCH_ASSOC) === ['maitrise' => 0, 'xp_rewarded' => 0], 'Foreign record reward flags changed');
    $result = $post('/chinois/ajax/toggle-vocabulaire-maitrise', ['id' => $wordIds['b'], 'csrf_token' => $tokens['b']]);
    $payload = json_decode($result['body'], true, 512, JSON_THROW_ON_ERROR);
    $check($result['status'] === 200 && $payload['data']['xpEarned'] === true, 'Second session did not grant independent XP');
    echo "PASS: two real HTTP sessions, private search/dashboard cache, independent mastery/XP/successes, foreign read/write/delete rejection, session CSRF isolation and removed SQL console.\n";
}
finally
{
    foreach ($cookies as $label => $cookie)
    {
        if (! isset($tokens[$label])) continue;
        http_set_cookie($cookie);
        http_post(http_base() . '/deconnexion', ['Content-Type: application/x-www-form-urlencoded'],
            http_build_query(['csrf_token' => $tokens[$label]]));
    }
    if ($accounts !== [])
    {
        $placeholders = implode(',', array_fill(0, count($accounts), '?'));
        $ids = array_values($accounts);
        $db->beginTransaction();
        try
        {
            foreach (['manga', 'artbook', 'figurine', 'nendoroid', 'peluche', 'chinois_grammaire', 'chinois_vocabulaire', 'manga_series_rewards', 'achievement_xp_rewards'] as $table)
            {
                $db->prepare("DELETE FROM $table WHERE user_id IN ($placeholders)")->execute($ids);
            }
            $db->prepare("DELETE FROM users WHERE id IN ($placeholders)")->execute($ids);
            $db->commit();
        }
        catch (Throwable $error)
        {
            $db->rollBack();
            throw $error;
        }
        foreach ($ids as $id) \Framework\Cache\Cache::forget(\App\Cache\CacheKey::dashboard($id));
    }
}
