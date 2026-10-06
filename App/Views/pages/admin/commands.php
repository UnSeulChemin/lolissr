<?php
declare(strict_types=1);
use App\DTO\Common\Responses\ViewData;
/** @var ViewData $view */
$job = $job ?? ['state' => 'idle', 'updated' => 0];
$output = $output ?? '';
$releaseJob = $releaseJob ?? ['state' => 'idle', 'updated' => 0];
$releaseOutput = $releaseOutput ?? '';
$releaseBusy = in_array($releaseJob['state'], ['queued', 'running'], true);
$labels = ['idle' => 'Prêt', 'queued' => 'En attente', 'running' => 'En cours', 'done' => 'Prêt', 'failed' => 'Échec', 'interrupted' => 'Interrompu'];
$busy = in_array($job['state'], ['queued', 'running'], true);
?>
<section class="layout-container dashboard-page" data-admin-live data-status-url="<?= e($view->baseUri) ?>admin/commandes/etat">
    <section class="dashboard-grid u-grid u-justify-center">
        <article class="card dashboard-card u-stack u-relative u-clip u-border-box admin-command-card" data-job="releases">
            <span class="dashboard-card-icon u-row-center" aria-hidden="true">📅</span>
            <span class="dashboard-card-title u-relative u-w-full u-bold">Sorties manga</span>
            <p class="dashboard-card-description">Actualiser les prochaines sorties et les couvertures des mangas de vos collections.</p>
            <p role="status"><?= e($labels[$releaseJob['state']] ?? 'État inconnu') ?></p>
            <form method="post" action="<?= e($view->baseUri) ?>admin/commandes/sorties">
                <?= csrf_field() ?>
                <button type="submit" class="form-submit u-inline-center u-pointer u-semibold" <?= $releaseBusy ? 'disabled' : '' ?>>Actualiser tous les comptes</button>
            </form>
            <form method="post" action="<?= e($view->baseUri) ?>admin/commandes/sorties/mon-compte">
                <?= csrf_field() ?>
                <button type="submit" class="form-submit form-submit-secondary u-inline-center u-pointer u-semibold" <?= $releaseBusy ? 'disabled' : '' ?>>Actualiser mon compte</button>
            </form>
        </article>
        <article class="card dashboard-card u-stack u-relative u-clip u-border-box admin-command-card" data-job="recommendations">
            <span class="dashboard-card-icon u-row-center" aria-hidden="true">📚</span>
            <span class="dashboard-card-title u-relative u-w-full u-bold">Recommandations manga</span>
            <p class="dashboard-card-description">Actualiser le catalogue et les couvertures pour tous les comptes.</p>
            <p role="status"><?= e($labels[$job['state']] ?? 'État inconnu') ?></p>
            <form method="post" action="<?= e($view->baseUri) ?>admin/commandes/recommandations">
                <?= csrf_field() ?>
                <button type="submit" class="form-submit u-inline-center u-pointer u-semibold" <?= $busy ? 'disabled' : '' ?>>Actualiser tous les comptes</button>
            </form>
            <form method="post" action="<?= e($view->baseUri) ?>admin/commandes/recommandations/mon-compte">
                <?= csrf_field() ?>
                <button type="submit" class="form-submit form-submit-secondary u-inline-center u-pointer u-semibold" <?= $busy ? 'disabled' : '' ?>>Actualiser mon compte</button>
            </form>
        </article>
    </section>
    <?php if ($output !== ''): ?>
        <section class="card admin-command-journal" hidden data-admin-journal="recommendations" data-journal-version="<?= e((string) $job['updated']) ?>">
            <button type="button" class="admin-journal-close" data-close-admin-journal aria-label="Fermer le journal des recommandations">×</button>
            <pre class="admin-command-output" aria-label="Journal de l’actualisation"><?= e($output) ?></pre>
        </section>
    <?php endif; ?>
    <?php if ($releaseOutput !== ''): ?>
        <section class="card admin-command-journal" hidden data-admin-journal="releases" data-journal-version="<?= e((string) $releaseJob['updated']) ?>">
            <button type="button" class="admin-journal-close" data-close-admin-journal aria-label="Fermer le journal des sorties manga">×</button>
            <pre class="admin-command-output" aria-label="Journal des sorties manga"><?= e($releaseOutput) ?></pre>
        </section>
    <?php endif; ?>
</section>
