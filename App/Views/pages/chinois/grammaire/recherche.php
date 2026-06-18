<?php

declare(strict_types=1);

/** @var App\DTO\Chinois\Responses\ChinoisGrammaireData $grammaire */

?>

<h1>
    <?= e($grammaire->titre) ?>
</h1>

<p>
    <?= e($grammaire->structure) ?>
</p>

<p>
    <?= e($grammaire->phrase) ?>
</p>

<p>
    <?= e($grammaire->traduction) ?>
</p>