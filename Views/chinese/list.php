<section class="section-content">

    <h2>Chinese List</h2>

    <?= $chineseForm; ?>

    <!-- Javascrit Event -->
    <section class="flex-center-center-gap-50 m-t-40">
        <a id="chEventHide" class="link-event">Masquer</a>
        <a id="chEventShow" class="link-event none">Afficher</a>
        <a id="chPrint" class="link-event none">Imprimer</a>
    </section>

    <section class="section-table">

        <article class="table-content flex-center-center">
            <p class="table-colonne table-width-20">Mot</p>
            <p class="table-colonne table-width-20">Type</p>
            <p class="table-colonne table-width-20">Traduction FR</p>
            <p class="table-colonne table-width-20">Traduction EN</p>
            <p class="table-colonne table-width-20">Exemple</p>
        </article>

        <?php foreach($chineses as $chinese): ?>
            <article class="table-content flex-center-center">
                <div class="table-width-20 flex-center-center table-crud">
                    <a href="<?= $pathRedirect; ?>chinese/update/<?= $chinese->id ?>">
                        <i class="fa-solid fa-pen table-crud-update"></i>
                    </a>
                    <a href="<?= $pathRedirect; ?>chinese/delete/<?= $chinese->id ?>">
                        <i class="fa-solid fa-trash table-crud-delete"></i>
                    </a>
                    <p><?= $chinese->word ?></p>
                </div>
                <div class="table-width-20">
                    <p><?= $chinese->type ?></p>
                </div>
                <div class="table-width-20">
                    <p><?= $chinese->french ?></p>
                </div>
                <div class="table-width-20">
                    <p><?= $chinese->english ?></p>
                </div>
                <div class="table-width-20">
                    <p><?= $chinese->example ?></p>
                </div>
            </article>
        <?php endforeach; ?>

    </section>

    <nav id="chNav" class="flex-center-center-gap-25 m-t-30">
        <?php
        $base = basename($_GET["p"]);
        if (!is_numeric($base)): $base = 1; endif;

        for ($getId = 1; $getId <= $count; $getId++):
            if ($base != $getId): 
                ?><a class="link-paginate" href="<?= $pathRedirect; ?>chinese/page/<?= $getId; ?>"><?= $getId; ?></a><?php
            else: 
                ?><a class="link-paginate active"><?= $getId; ?></a><?php
            endif;
        endfor; ?>
    </nav>

    <div id="chHistory" class="m-t-30">
        <a class="link-section" href="javascript:history.go(-1)">Back</a>
    </div>

</section>

<script>
// get const for trigger event
const chEventHide = document.getElementById('chEventHide');
chEventHide.addEventListener('click', hide)

// get const for trigger event
const chEventShow = document.getElementById('chEventShow');
chEventShow.addEventListener('click', show)

// get const for trigger event
const chPrint = document.getElementById('chPrint');
chPrint.addEventListener('click', printDoc)

// get const
const chineseForm = document.getElementById('chineseForm');
const chNav = document.getElementById('chNav');
const chHistory = document.getElementById('chHistory');
const header = document.getElementsByTagName('header')[0];
const section = document.getElementsByTagName('section')[0];
const title = document.getElementsByTagName('h2')[0];

// function hide
function hide()
{
    // css
    chineseForm.style.display = 'none';
    chNav.style.display = 'none';
    chHistory.style.display = 'none';
    header.style.display = 'none';
    section.style.margin = '0';

    // html
    title.innerHTML = 'Chinese Impression';

    // function
    chEventHide.style.display = 'none';
    chEventShow.style.display = 'block';
    chPrint.style.display = 'block';
}

// function show
function show()
{
    // css
    chineseForm.style.display = 'block';
    chNav.style.display = 'flex';
    chHistory.style.display = 'block';
    header.style.display = 'block';
    section.style.margin = '30px 0';

    // html
    title.innerHTML = 'Chinese List';

    // function
    chEventHide.style.display = 'block';
    chEventShow.style.display = 'none';
    chPrint.style.display = 'none';
}

// function printDoc
function printDoc()
{
    window.print()
}
</script>