<?php
declare(strict_types=1);
use App\DTO\Common\Responses\ViewData;
/** @var ViewData $view */
$job = $job ?? ['state' => 'idle', 'updated' => 0];
$output = $output ?? '';
$busy = in_array($job['state'], ['queued', 'running'], true);
$labels = ['idle' => 'Prêt', 'queued' => 'En attente', 'running' => 'En cours', 'done' => 'Prêt', 'failed' => 'Échec', 'interrupted' => 'Interrompu'];
$actions = [
    ['key' => 'doctor', 'icon' => '🩺', 'title' => 'Diagnostic', 'description' => 'Vérifier la configuration PHP, les services et les assets.', 'button' => 'Lancer le diagnostic'],
    ['key' => 'assets', 'icon' => '🛠️', 'title' => 'Assets', 'description' => 'Compiler le CSS et le JavaScript et actualiser leurs versions.', 'button' => 'Compiler les assets'],
    ['key' => 'images-check', 'icon' => '🔍', 'title' => 'Vérification images', 'description' => 'Vérifier profils, miniatures, variantes et empreintes sans modifier les fichiers.', 'button' => 'Vérifier les images'],
    ['key' => 'images', 'icon' => '🖼️', 'title' => 'Images', 'description' => 'Optimiser profils et miniatures, générer les variantes et actualiser les empreintes.', 'button' => 'Optimiser les images'],
    ['key' => 'cache', 'icon' => '🧹', 'title' => 'Cache', 'description' => 'Vider le cache de l’application.', 'button' => 'Vider le cache'],
    ['key' => 'reset', 'icon' => '🔄', 'title' => 'Reset Dev', 'description' => 'Vider les logs, le cache et toutes les sessions, puis régénérer l’autoload. Déconnecte tous les comptes. Composer requis sur le serveur.', 'button' => 'Nettoyer et déconnecter tous les comptes'],
    ['key' => 'migrations-create', 'icon' => '📝', 'title' => 'Créer une migration', 'description' => 'Créer un fichier SQL vide nommé create dans scripts/Database/migrations/. Écrire le SQL dans ce fichier avant de l’appliquer.', 'button' => 'Créer le fichier SQL'],
    ['key' => 'migrations-check', 'icon' => '🔍', 'title' => 'Vérification migrations', 'description' => 'Afficher les migrations appliquées et celles en attente, sans modifier la base.', 'button' => 'Vérifier les migrations'],
    ['key' => 'migrations', 'icon' => '🗄️', 'title' => 'Migrations', 'description' => 'Appliquer les migrations SQL manquantes à la base de données du site.', 'button' => 'Appliquer les migrations']
];
?>
<section class="layout-container dashboard-page" data-admin-live data-status-url="<?= e($view->baseUri) ?>admin/commandes/etat">
    <section class="dashboard-grid u-grid u-justify-center">
        <?php foreach ($actions as $action): ?>
            <article class="card dashboard-card u-stack u-relative u-clip u-border-box admin-command-card" data-job="maintenance">
                <span class="dashboard-card-icon u-row-center" aria-hidden="true"><?= e($action['icon']) ?></span>
                <span class="dashboard-card-title u-relative u-w-full u-bold"><?= e($action['title']) ?></span>
                <code class="admin-command-badge"><?= e(match ($action['key'])
                { 'doctor' => 'composer doctor', 'assets' => 'composer assets:build', 'images-check' => 'composer images:check', 'images' => 'composer images:build', 'reset' => 'composer dev:reset', 'migrations-create' => 'composer db:migrate:create -- create', 'migrations-check' => 'composer db:migrate:check', 'migrations' => 'composer db:migrate', default => 'composer cache:clear' }) ?></code>
                <p class="dashboard-card-description"><?= e($action['description']) ?></p>
                <p role="status"><?= e($labels[$job['state']] ?? 'État inconnu') ?></p>
                <form method="post" action="<?= e($view->baseUri . 'admin/dev/commandes/' . $action['key']) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="form-submit u-inline-center u-pointer u-semibold" <?= $busy ? 'disabled' : '' ?>><?= e($action['button']) ?></button>
                </form>
            </article>
        <?php endforeach; ?>
    </section>
    <?php if ($output !== ''): ?>
        <section class="card admin-command-journal" hidden data-admin-journal="maintenance" data-journal-version="<?= e((string) $job['updated']) ?>">
            <button type="button" class="admin-journal-close" data-close-admin-journal aria-label="Fermer le journal de maintenance">×</button>
            <pre class="admin-command-output"><?= e($output) ?></pre>
        </section>
    <?php endif; ?>
</section>
