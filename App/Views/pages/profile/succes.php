<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */
/** @var list<array{category: string, icon: string, title: string, current: int, target: int, unlocked: bool}> $achievements */
$unlockedCount = count(array_filter($achievements, static fn (array $item): bool => $item['unlocked']));
$category = '';
?>

<section class="success-page">
    <header class="card success-heading">
        <h1>🏆 Mes succès</h1>
        <p>Retrouve les étapes de ta collection, de tes lectures et de ton apprentissage.</p>
        <strong><?= $unlockedCount ?> / <?= count($achievements) ?> succès obtenus</strong>
        <p class="success-note">La progression suit tes statistiques actuelles.</p>
    </header>

    <?php foreach ($achievements as $achievement): ?>
        <?php if ($category !== $achievement['category']): ?>
            <?php if ($category !== ''): ?></div></section><?php endif; ?>
            <?php $category = $achievement['category']; ?>
            <section class="success-category">
                <h2><?= e($achievement['icon'] . ' ' . $category) ?></h2>
                <div class="success-grid">
        <?php endif; ?>
        <article class="card success-item <?= $achievement['unlocked'] ? 'is-unlocked' : 'is-locked' ?>">
            <span class="success-icon" aria-hidden="true"><?= e($achievement['icon']) ?></span>
            <h3><?= e($achievement['title']) ?></h3>
            <p class="success-status"><?= $achievement['unlocked'] ? '✓ Obtenu' : '🔒 À débloquer' ?></p>
            <progress max="<?= $achievement['target'] ?>" value="<?= min($achievement['current'], $achievement['target']) ?>" aria-label="<?= e($achievement['title']) ?>"></progress>
            <span><?= min($achievement['current'], $achievement['target']) ?> / <?= $achievement['target'] ?></span>
        </article>
    <?php endforeach; ?>
    <?php if ($category !== ''): ?></div></section><?php endif; ?>
</section>
