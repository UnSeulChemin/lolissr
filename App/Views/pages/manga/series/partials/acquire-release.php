<?php
declare(strict_types=1);
/** @var \App\DTO\Common\Responses\ViewData $view */
/** @var \App\DTO\Manga\Responses\UpcomingMangaData $release */
/** @var string $releaseSlug */
?>
<?php if (!$release->isUpcoming && $release->imageUrl !== null): ?>
    <form class="js-acquire-release" method="post" action="<?= e($view->baseUri . 'manga/series/' . rawurlencode($releaseSlug) . '/posseder/' . $release->number) ?>">
        <?= csrf_field() ?>
        <button class="form-submit u-inline-center u-pointer u-semibold u-w-full" type="submit">Je possède ce tome</button>
    </form>
<?php endif; ?>
