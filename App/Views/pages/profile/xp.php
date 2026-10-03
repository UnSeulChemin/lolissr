<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;

use Framework\Support\Str;

/** @var ViewData $view */
/** @var string $section */

/** @var list<array{category: string, icon: string, title: string, current: int, target: int, unlocked: bool}> $achievements */
/** @var int $level */
/** @var int $currentXp */
/** @var int $xpRequired */
/** @var int $readTomes */
/** @var int $tomeXp */
/** @var int $completedSeries */
/** @var int $seriesXp */
/** @var int $readArtbooks */
/** @var int $artbookXp */
/** @var int $figurinesCollected */
/** @var int $figurinesXp */
/** @var int $nendoroidsCollected */
/** @var int $nendoroidsXp */
/** @var int $peluchesCollected */
/** @var int $peluchesXp */
/** @var int $vocabularyLearned */
/** @var int $vocabularyXp */
/** @var int $grammarLearned */
/** @var int $grammarXp */
/** @var int $totalProfileXp */
/** @var int $achievementXp */

$filters = ['tout' => '✨'];
foreach ($achievements as $item)
{
    if (in_array($item['category'], ['Niveau', 'Succès'], true)) continue;
    $filters[$item['category']] = $item['icon'];
}
$filters['succes'] = '🏆';

?>
<section class="layout-container profile-page profile-xp-page u-stack">
    <header class="card profile-xp-heading">
        <h1>📊 Mon expérience</h1>
        <strong>Niveau <?= $level ?></strong>
        <p><?= number_format($currentXp, 0, ',', ' ') ?> / <?= number_format($xpRequired, 0, ',', ' ') ?> XP</p>
        <progress value="<?= $currentXp ?>" max="<?= max(1, $xpRequired) ?>" aria-label="Progression vers le prochain niveau"></progress>
        <p>Retrouve l’XP gagnée grâce à tes lectures, ta collection, ton apprentissage et tes succès.</p>
        <p class="profile-xp-note">Progression vers le niveau <?= $level + 1 ?>.</p>
    </header>

        <nav class="card profile-summary profile-xp-filters" aria-label="Filtrer les statistiques d’expérience">
            <h2>Sommaire</h2>
            <div class="profile-summary-links">
            <?php foreach ($filters as $key => $icon): ?>
                <?php $label = match ($key)
                { 'tout' => 'Tout', 'succes' => 'Succès', 'Vocabulaire' => 'Vocabulaires', 'Grammaire' => 'Grammaires', default => $key }; ?>
                <a href="<?= e($view->baseUri . 'profil/xp' . ($key === 'tout' ? '' : '/' . Str::asciiSlug($key))) ?>"
                   class="profile-summary-link" aria-label="<?= e($label) ?>" title="<?= e($label) ?>"
                   <?= $section === $key ? 'aria-current="page"' : '' ?>><span aria-hidden="true"><?= e($icon) ?></span><span><?= e($label) ?></span></a>
            <?php endforeach; ?>
            </div>
        </nav>
    <section class="profile-stats u-stack">

        <div class="profile-stat-row u-grid">

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📈 Progression totale
                </h2>

                <p class="profile-stat-value u-bold">
                    Niveau <?= $level ?>
                </p>

            </article>

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Totale
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($totalProfileXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

        <?php if ($section === 'tout' || $section === 'Tomes'): ?>
        <div class="profile-stat-row u-grid">

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📚 Tomes lus
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($readTomes) ?>
                </p>

            </article>

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📚 XP Tomes
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($tomeXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>
        <?php endif; ?>

        <?php if ($section === 'tout' || $section === 'Séries'): ?>
        <div class="profile-stat-row u-grid">

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📖 Séries terminées
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($completedSeries) ?>
                </p>

            </article>

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    🏆 XP Séries
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($seriesXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>
        <?php endif; ?>

        <?php if ($section === 'tout' || $section === 'Artbooks'): ?>
        <div class="profile-stat-row u-grid">

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📕 Artbooks lus
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($readArtbooks) ?>
                </p>

            </article>

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Artbooks
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($artbookXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>
        <?php endif; ?>

        <?php if ($section === 'tout' || $section === 'Figurines'): ?>
        <div class="profile-stat-row u-grid">

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    🎀 Figurines
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($figurinesCollected) ?>
                </p>

            </article>

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Figurines
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($figurinesXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>
        <?php endif; ?>

        <?php if ($section === 'tout' || $section === 'Nendoroids'): ?>
        <div class="profile-stat-row u-grid">

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    🪆 Nendoroids
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($nendoroidsCollected) ?>
                </p>

            </article>

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Nendoroids
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($nendoroidsXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>
        <?php endif; ?>

        <?php if ($section === 'tout' || $section === 'Peluches'): ?>
        <div class="profile-stat-row u-grid">

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    🧸 Peluches
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($peluchesCollected) ?>
                </p>

            </article>

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Peluches
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($peluchesXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>
        <?php endif; ?>

        <?php if ($section === 'tout' || $section === 'Vocabulaire'): ?>
        <div class="profile-stat-row u-grid">

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    🎓 Vocabulaires appris
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($vocabularyLearned) ?>
                </p>

            </article>

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Vocabulaires
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($vocabularyXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>
        <?php endif; ?>

        <?php if ($section === 'tout' || $section === 'Grammaire'): ?>
        <div class="profile-stat-row u-grid">

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📝 Grammaires
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($grammarLearned) ?>
                </p>

            </article>

            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Grammaires
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($grammarXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>
        <?php endif; ?>

        <?php if ($section === 'tout' || $section === 'succes'): ?>
        <div class="profile-stat-row profile-stat-row-achievements u-grid">
            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">
                <h2 class="profile-stat-title u-bold">🏆 Succès</h2>
                <p class="profile-stat-value u-bold"><?= count(array_filter($achievements, static fn (array $achievement): bool => $achievement['unlocked'])) ?></p>
            </article>
            <article class="card profile-stat-card u-justify-center u-w-full u-border-box">
                <h2 class="profile-stat-title u-bold">🏆 XP Succès</h2>
                <p class="profile-stat-value u-bold"><?= number_format($achievementXp, 0, ',', ' ') ?> XP</p>
            </article>
        </div>
        <?php endif; ?>
    </section>

</section>
