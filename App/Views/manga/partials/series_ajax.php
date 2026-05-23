<?php
declare(strict_types=1);

if (empty($mangas)) {
    echo '<p class="collection-empty">Aucun manga trouvé.</p>';
    return;
}

$baseUri = $baseUri ?? '';
$isSerieView = $isSerieView ?? false;
?>

<section class="collection-grid animate-fade-up-stagger">

<?php foreach ($mangas as $manga):
    $slug       = (string) ($manga->slug ?? '');
    $numero     = (int) ($manga->numero ?? 0);
    $livre      = (string) ($manga->livre ?? '');
    $thumbnail  = $manga->thumbnail ?? null;
    $extension  = $manga->extension ?? null;
    $statut     = (string) ($manga->statut ?? 'en_cours');
    $note       = $manga->note ?? null;
    $averageNote= $manga->averageNote ?? $manga->average_note ?? null;
    $total      = (int) ($manga->total ?? 0);

    if (!$slug || !$livre || !$thumbnail || !$extension) continue;

    $href = $isSerieView
        ? "{$baseUri}manga/series/{$slug}/{$numero}"
        : "{$baseUri}manga/series/{$slug}";

    $thumbnailPath = "{$baseUri}images/mangas/thumbnail/{$thumbnail}.{$extension}";

    $displayNote = $isSerieView ? $note : $averageNote;
    $noteClass = 'collection-note-mid';
    if ($displayNote !== null) {
        $displayNote = (float) $displayNote;
        if ($displayNote >= 8) $noteClass = 'collection-note-good';
        elseif ($displayNote <= 4) $noteClass = 'collection-note-low';
    }
    $noteLabel = $displayNote !== null
        ? ($isSerieView ? (string)(int)$displayNote : number_format($displayNote, 1, ',', ''))
        : '0';

    $statutLabel = $statut === 'termine' ? 'Terminé' : 'En cours';
    $statutClass = $statut === 'termine' ? 'collection-status-finished' : 'collection-status-progress';
?>

<a class="card card-link collection-card-link" href="<?= e($href) ?>">

    <?php if (!$isSerieView): ?>
        <span class="collection-status-badge <?= e($statutClass) ?>"><?= e($statutLabel) ?></span>
    <?php endif; ?>

    <span class="collection-card-badge <?= e($noteClass) ?>">⭐ <?= e($noteLabel) ?>/10</span>

    <!-- SVG statique, pas de JS -->
    <span class="collection-read-badge">
        <svg class="collection-read-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M7 3C6.45 3 6 3.45 6 4V21L12 17L18 21V4C18 3.45 17.55 3 17 3H7Z"/>
        </svg>
    </span>

    <div class="card-image-box-portrait">
        <img class="card-image-portrait" src="<?= e($thumbnailPath) ?>" alt="<?= e($livre) ?>">
    </div>

    <p class="collection-card-title"><?= e($livre) ?></p>
    <p class="collection-card-subtitle">
        <?= $isSerieView ? 'Tome ' . str_pad((string)$numero, 2, '0', STR_PAD_LEFT) : $total . ' tomes' ?>
    </p>
</a>

<?php endforeach; ?>
</section>