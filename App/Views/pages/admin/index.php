<?php
declare(strict_types=1);
use App\DTO\Common\Responses\ViewData;
/** @var ViewData $view */
?>
<section class="layout-container dashboard-page">
    <section class="dashboard-grid u-grid u-justify-center">
        <a class="card transition-card dashboard-card u-stack u-relative u-clip u-border-box" href="<?= e($view->baseUri) ?>admin/dev">
            <span class="dashboard-card-icon u-row-center" aria-hidden="true">🗃️</span>
            <span class="dashboard-card-title u-relative u-w-full u-bold">Dev</span>
            <span class="dashboard-card-description u-relative u-w-full">Optimiser les images et vider le cache.</span>
        </a>
        <a class="card transition-card dashboard-card u-stack u-relative u-clip u-border-box" href="<?= e($view->baseUri) ?>admin/commandes">
            <span class="dashboard-card-icon u-row-center" aria-hidden="true">⚙️</span>
            <span class="dashboard-card-title u-relative u-w-full u-bold">Commandes</span>
            <span class="dashboard-card-description u-relative u-w-full">Lancer les actualisations et suivre leur résultat.</span>
        </a>
    </section>
</section>
