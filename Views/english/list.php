<section class="section-content">

    <h2>English List</h2>

    <?= $englishForm; ?>

    <!-- Javascrit Event -->
    <section class="flex-center-center-gap-50 m-t-40">
        <a id="enEventHide" class="link-event">Masquer</a>
        <a id="enEventShow" class="link-event none">Afficher</a>
        <a id="enPrint" class="link-event none">Imprimer</a>
    </section>

    <section class="section-table">

        <article class="table-content flex-center-center">
            <p class="table-colonne table-width-25">Mot</p>
            <p class="table-colonne table-width-25">Type</p>
            <p class="table-colonne table-width-25">Traduction FR</p>
            <p class="table-colonne table-width-25">Exemple</p>
        </article>

        <?php foreach($englishs as $english): ?>
            <article class="table-content flex-center-center">
                <div class="table-width-25 flex-center-center table-crud">
                    <a href="<?= $pathRedirect; ?>english/update/<?= $english->id ?>">
                        <i class="fa-solid fa-pen table-crud-update"></i>
                    </a>
                    <a href="<?= $pathRedirect; ?>english/delete/<?= $english->id ?>">
                        <i class="fa-solid fa-trash table-crud-delete"></i>
                    </a>
                    <p><?= $english->word ?></p>
                </div>
                <div class="table-width-25">
                    <p><?= $english->type ?></p>
                </div>
                <div class="table-width-25">
                    <p><?= $english->french ?></p>
                </div>
                <div class="table-width-25">
                    <p><?= $english->example ?></p>
                </div>
            </article>
        <?php endforeach; ?>

    </section>

    <nav id="enNav" class="flex-center-center-gap-25 m-t-30">
        <?php
        $base = basename($_GET["p"]);
        if (!is_numeric($base)): $base = 1; endif;

        for ($getId = 1; $getId <= $count; $getId++):
            if ($base != $getId): 
                ?><a class="link-paginate" href="<?= $pathRedirect; ?>english/page/<?= $getId; ?>"><?= $getId; ?></a><?php
            else: 
                ?><a class="link-paginate active"><?= $getId; ?></a><?php
            endif;
        endfor; ?>
    </nav>

    <div id="enHistory" class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>

<script>
// get const for trigger event
const enEventHide = document.getElementById('enEventHide');
enEventHide.addEventListener('click', hide)

// get const for trigger event
const enEventShow = document.getElementById('enEventShow');
enEventShow.addEventListener('click', show)

// get const for trigger event
const enPrint = document.getElementById('enPrint');
enPrint.addEventListener('click', printDoc)

// get const
const englishForm = document.getElementById('englishForm');
const enNav = document.getElementById('enNav');
const enHistory = document.getElementById('enHistory');
const header = document.getElementsByTagName('header')[0];
const section = document.getElementsByTagName('section')[0];
const title = document.getElementsByTagName('h2')[0];

// function hide
function hide()
{
    // css
    englishForm.style.display = 'none';
    enNav.style.display = 'none';
    enHistory.style.display = 'none';
    header.style.display = 'none';
    section.style.margin = '0';

    // html
    title.innerHTML = 'English Impression';

    // function
    enEventHide.style.display = 'none';
    enEventShow.style.display = 'block';
    enPrint.style.display = 'block';
}

// function show
function show()
{
    // css
    englishForm.style.display = 'block';
    enNav.style.display = 'flex';
    enHistory.style.display = 'block';
    header.style.display = 'block';
    section.style.margin = '30px 0';

    // html
    title.innerHTML = 'English List';

    // function
    enEventHide.style.display = 'block';
    enEventShow.style.display = 'none';
    enPrint.style.display = 'none';
}

// function printDoc
function printDoc()
{
    window.print()
}
</script>