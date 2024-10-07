<section class="section-content">

    <h2>Anime List</h2>

    <?= $animeForm; ?>

    <!-- Javascrit Event -->
    <section class="flex-center-center-gap-50 m-t-40">
        <a id="anEventHide" class="link-event">Masquer</a>
        <a id="anEventShow" class="link-event none">Afficher</a>
        <a id="anPrint" class="link-event none">Imprimer</a>
    </section>

    <section class="section-table">

        <article class="table-content flex-center-center">
            <p class="table-colonne table-width-20">Anime</p>
            <p class="table-colonne table-width-20">Origine</p>
            <p class="table-colonne table-width-15">Saison</p>
            <p class="table-colonne table-width-15">Épisode</p>
            <p class="table-colonne table-width-15">Fin</p>
            <p class="table-colonne table-width-15">Note</p>
        </article>

        <?php foreach($animes as $anime): ?>
            <article class="table-content flex-center-center">
                <div class="table-width-20 flex-center-center table-crud">
                    <a href="<?= $pathRedirect; ?>anime/update/<?= $anime->id ?>">
                        <i class="fa-solid fa-pen table-crud-update"></i>
                    </a>
                    <a href="<?= $pathRedirect; ?>anime/delete/<?= $anime->id ?>">
                        <i class="fa-solid fa-trash table-crud-delete"></i>
                    </a>
                    <p><?= $anime->anime ?></p>
                </div>
                <div class="table-width-20">
                    <p><?= $anime->origin ?></p>
                </div>
                <div class="table-width-15">
                    <p><?= $anime->season ?></p>
                </div>
                <div class="table-width-15">
                    <p><?= $anime->episode ?></p>
                </div>
                <div class="table-width-15">
                    <p><?= $anime->end ?></p>
                </div>
                <div class="table-width-15">
                    <p><?= $anime->note ?> / 5</p>
                </div>
            </article>
        <?php endforeach; ?>

    </section>

    <nav id="anNav" class="flex-center-center-gap-25 m-t-30">
        <?php
        $base = basename($_GET["p"]);
        if (!is_numeric($base)): $base = 1; endif;

        for ($getId = 1; $getId <= $count; $getId++):
            if ($base != $getId): 
                ?><a class="link-paginate" href="<?= $pathRedirect; ?>anime/page/<?= $getId; ?>"><?= $getId; ?></a><?php
            else: 
                ?><a class="link-paginate active"><?= $getId; ?></a><?php
            endif;
        endfor; ?>
    </nav>

    <div id="anHistory" class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>

<script>
// get const for trigger event
const anEventHide = document.getElementById('anEventHide');
anEventHide.addEventListener('click', hide)

// get const for trigger event
const anEventShow = document.getElementById('anEventShow');
anEventShow.addEventListener('click', show)

// get const for trigger event
const anPrint = document.getElementById('anPrint');
anPrint.addEventListener('click', printDoc)

// get const
const animeForm = document.getElementById('animeForm');
const anNav = document.getElementById('anNav');
const anHistory = document.getElementById('anHistory');
const header = document.getElementsByTagName('header')[0];
const section = document.getElementsByTagName('section')[0];
const title = document.getElementsByTagName('h2')[0];

// function hide
function hide()
{
    // css
    animeForm.style.display = 'none';
    anNav.style.display = 'none';
    anHistory.style.display = 'none';
    header.style.display = 'none';
    section.style.margin = '0';

    // html
    title.innerHTML = 'Anime Impression';

    // function
    anEventHide.style.display = 'none';
    anEventShow.style.display = 'block';
    anPrint.style.display = 'block';
}

// function show
function show()
{
    // css
    animeForm.style.display = 'block';
    anNav.style.display = 'flex';
    anHistory.style.display = 'block';
    header.style.display = 'block';
    section.style.margin = '30px 0';

    // html
    title.innerHTML = 'Anime List';

    // function
    anEventHide.style.display = 'block';
    anEventShow.style.display = 'none';
    anPrint.style.display = 'none';
}

// function printDoc
function printDoc()
{
    window.print()
}
</script>