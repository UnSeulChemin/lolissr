<?php

declare(strict_types=1);

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\DTO\Common\Responses\ViewData;

/** @var ChinoisVocabulaireData $vocabulaire */
/** @var ViewData $view */

$vocabulaires = [$vocabulaire];

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-content collection-ajax-content--single"
        data-vocabulary-url="<?= e($view->baseUri . 'chinois/vocabulaire/' . $vocabulaire->langue) ?>">

        <?php require view_path('pages/chinois/vocabulaire/partials/items.php'); ?>

    </div>

</section>
