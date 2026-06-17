<?php

declare(strict_types=1);

/** @var App\DTO\Chinois\Responses\ChinoisVocabulaireData $vocabulaire */

?>

<h1>
    <?= e($vocabulaire->mot) ?>
</h1>

<p>
    <?= e($vocabulaire->pinyin) ?>
</p>

<p>
    <?= e($vocabulaire->traduction) ?>
</p>