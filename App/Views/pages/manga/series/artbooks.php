<?php

declare(strict_types=1);

/** @var list<App\Models\Artbook> $artbooks */

?>

<h1>Artbooks</h1>

<?php foreach ($artbooks as $artbook): ?>

    <p>
        <?= e($artbook->artbook) ?>
    </p>

<?php endforeach; ?>