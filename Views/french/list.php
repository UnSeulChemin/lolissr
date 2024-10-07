<section class="section-content">

    <h2>French List</h2>

    <?= $frenchForm; ?>

    <!-- Javascrit Event -->
    <section class="flex-center-center-gap-50 m-t-40">
        <a id="frEventHide" class="link-event">Masquer</a>
        <a id="frEventShow" class="link-event none">Afficher</a>
        <a id="frPrint" class="link-event none">Imprimer</a>
    </section>

    <section class="section-table">

        <article class="table-content flex-center-center">
            <p class="table-colonne table-width-25">Mot</p>
            <p class="table-colonne table-width-25">Type</p>
            <p class="table-colonne table-width-25">Définition</p>
            <p class="table-colonne table-width-25">Exemple</p>
        </article>

        <?php foreach($frenchs as $french): ?>
            <article class="table-content flex-center-center">
                <div class="table-width-25 flex-center-center table-crud">
                    <a href="<?= $pathRedirect; ?>french/update/<?= $french->id ?>">
                        <i class="fa-solid fa-pen table-crud-update"></i>
                    </a>
                    <a href="<?= $pathRedirect; ?>french/delete/<?= $french->id ?>">
                        <i class="fa-solid fa-trash table-crud-delete"></i>
                    </a>
                    <p><?= $french->word ?></p>
                </div>
                <div class="table-width-25">
                    <p><?= $french->type ?></p>
                </div>
                <div class="table-width-25">
                    <p><?= $french->definition ?></p>
                </div>
                <div class="table-width-25">
                    <p><?= $french->example ?></p>
                </div>
            </article>
        <?php endforeach; ?>

    </section>

    <nav id="frNav" class="flex-center-center-gap-25 m-t-30">
        <?php
        $base = basename($_GET["p"]);
        if (!is_numeric($base)): $base = 1; endif;

        for ($getId = 1; $getId <= $count; $getId++):
            if ($base != $getId): 
                ?><a class="link-paginate" href="<?= $pathRedirect; ?>french/page/<?= $getId; ?>"><?= $getId; ?></a><?php
            else: 
                ?><a class="link-paginate active"><?= $getId; ?></a><?php
            endif;
        endfor; ?>
    </nav>

    <div id="frHistory"  class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>

<script>
// get const for trigger event
const frEventHide = document.getElementById('frEventHide');
frEventHide.addEventListener('click', hide)

// get const for trigger event
const frEventShow = document.getElementById('frEventShow');
frEventShow.addEventListener('click', show)

// get const for trigger event
const frPrint = document.getElementById('frPrint');
frPrint.addEventListener('click', printDoc)

// get const
const frenchForm = document.getElementById('frenchForm');
const frNav = document.getElementById('frNav');
const frHistory = document.getElementById('frHistory');
const header = document.getElementsByTagName('header')[0];
const section = document.getElementsByTagName('section')[0];
const title = document.getElementsByTagName('h2')[0];

// function hide
function hide()
{
    // css
    frenchForm.style.display = 'none';
    frNav.style.display = 'none';
    frHistory.style.display = 'none';
    header.style.display = 'none';
    section.style.margin = '0';

    // html
    title.innerHTML = 'French Impression';

    // function
    frEventHide.style.display = 'none';
    frEventShow.style.display = 'block';
    frPrint.style.display = 'block';
}

// function show
function show()
{
    // css
    frenchForm.style.display = 'block';
    frNav.style.display = 'flex';
    frHistory.style.display = 'block';
    header.style.display = 'block';
    section.style.margin = '30px 0';

    // html
    title.innerHTML = 'French List';

    // function
    frEventHide.style.display = 'block';
    frEventShow.style.display = 'none';
    frPrint.style.display = 'none';
}

// function printDoc
function printDoc()
{
    window.print()
}
</script>