<section class="section-content">

    <h2>Manga List</h2>

    <?= $mangaForm; ?>

    <!-- Javascrit Event -->
    <section class="flex-center-center-gap-50 m-t-40">
        <a id="maEventHide" class="link-event">Masquer</a>
        <a id="maEventShow" class="link-event none">Afficher</a>
        <a id="maPrint" class="link-event none">Imprimer</a>
    </section>

    <section class="section-table">

        <article class="table-content flex-center-center">
            <p class="table-colonne table-width-20">Manga</p>
            <p class="table-colonne table-width-20">Maison d'édition</p>
            <p class="table-colonne table-width-20">Tome</p>
            <p class="table-colonne table-width-20">Suivant</p>
            <p class="table-colonne table-width-20">Fin</p>
        </article>

        <?php foreach($mangas as $manga): ?>
            <article class="table-content flex-center-center">
                <div class="table-width-20 flex-center-center table-crud">
                    <a href="<?= $pathRedirect; ?>manga/update/<?= $manga->id ?>">
                        <i class="fa-solid fa-pen table-crud-update"></i>
                    </a>
                    <a href="<?= $pathRedirect; ?>manga/delete/<?= $manga->id ?>">
                        <i class="fa-solid fa-trash table-crud-delete"></i>
                    </a>
                    <p><?= $manga->manga ?></p>
                </div>
                <div class="table-width-20">
                    <p><?= $manga->house ?></p>
                </div>
                <div class="table-width-20">
                    <p><?= $manga->tome ?></p>
                </div>
                <div class="table-width-20">
                    <p><?= $manga->next ?></p>
                </div>
                <div class="table-width-20">
                    <p><?= $manga->end ?></p>
                </div>
            </article>
        <?php endforeach; ?>

    </section>

    <nav id="maNav" class="flex-center-center-gap-25 m-t-30">
        <?php
        $base = basename($_GET["p"]);
        if (!is_numeric($base)): $base = 1; endif;

        for ($getId = 1; $getId <= $count; $getId++):
            if ($base != $getId): 
                ?><a class="link-paginate" href="<?= $pathRedirect; ?>manga/page/<?= $getId; ?>"><?= $getId; ?></a><?php
            else: 
                ?><a class="link-paginate active"><?= $getId; ?></a><?php
            endif;
        endfor; ?>
    </nav>

    <div id="maHistory"  class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>

<script>
// get const for trigger event
const maEventHide = document.getElementById('maEventHide');
maEventHide.addEventListener('click', hide)

// get const for trigger event
const maEventShow = document.getElementById('maEventShow');
maEventShow.addEventListener('click', show)

// get const for trigger event
const maPrint = document.getElementById('maPrint');
maPrint.addEventListener('click', printDoc)

// get const
const mangaForm = document.getElementById('mangaForm');
const maNav = document.getElementById('maNav');
const maHistory = document.getElementById('maHistory');
const header = document.getElementsByTagName('header')[0];
const section = document.getElementsByTagName('section')[0];
const title = document.getElementsByTagName('h2')[0];

// function hide
function hide()
{
    // css
    mangaForm.style.display = 'none';
    maNav.style.display = 'none';
    maHistory.style.display = 'none';
    header.style.display = 'none';
    section.style.margin = '0';

    // html
    title.innerHTML = 'Manga Impression';

    // function
    maEventHide.style.display = 'none';
    maEventShow.style.display = 'block';
    maPrint.style.display = 'block';
}

// function show
function show()
{
    // css
    mangaForm.style.display = 'block';
    maNav.style.display = 'flex';
    maHistory.style.display = 'block';
    header.style.display = 'block';
    section.style.margin = '30px 0';

    // html
    title.innerHTML = 'Manga List';

    // function
    maEventHide.style.display = 'block';
    maEventShow.style.display = 'none';
    maPrint.style.display = 'none';
}

// function printDoc
function printDoc()
{
    window.print()
}
</script>