<?php

declare(strict_types=1);

$artbooks =
    is_array($artbooks ?? null)
        ? $artbooks
        : [];

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path(
            'pages/manga/artbooks/ajax.php',
        ); ?>

    </div>

</section>