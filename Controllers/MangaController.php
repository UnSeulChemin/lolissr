<?php
namespace App\Controllers;

use App\Models\MangaModel;
use App\Core\Form;
use App\Core\Functions;

class MangaController extends Controller
{
    /**
     * route /manga
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | Manga';
        $this->render('manga/index');
    }

    /**
     * route /manga/list
     * @return void
     */
    public function list(): void
    {
        // environment variables
        $manga = isset($_POST['manga']) ? strip_tags($_POST['manga']) : '';
        $house = isset($_POST['house']) ? strip_tags($_POST['house']) : '';
        $tome = isset($_POST['tome']) ? strip_tags($_POST['tome']) : '';
        $next = isset($_POST['next']) ? strip_tags($_POST['next']) : '';
        $end = isset($_POST['end']) ? strip_tags($_POST['end']) : '';

        // form validate
        if (Form::validate($_POST, ['manga', 'house', 'tome', 'next', 'end'])):
            $mangaModel = new MangaModel;
            $mangaModel->setManga($manga)->setHouse($house)->setTome($tome)
                ->setNext($next)->setEnd($end);
            if ($mangaModel->create()):
                header('Location: list'); exit;
            endif;
        endif;

        // form create
        $form = self::mangaForm($manga, $house, $tome, $next, $end);

        // class instance
        $mangaModel = new MangaModel;
        $mangas = $mangaModel->findAllPaginate('end DESC', 18, 1);
        $count = $mangaModel->countPaginate(18);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Manga List';
        $this->render('manga/list', ['mangaForm' => $form->create(), 'mangas' => $mangas,
            'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /manga/page/{id}
     * @param int $id
     * @return void
     */
    public function page(int $id): void
    {
        // environment variables
        $manga = isset($_POST['manga']) ? strip_tags($_POST['manga']) : '';
        $house = isset($_POST['house']) ? strip_tags($_POST['house']) : '';
        $tome = isset($_POST['tome']) ? strip_tags($_POST['tome']) : '';
        $next = isset($_POST['next']) ? strip_tags($_POST['next']) : '';
        $end = isset($_POST['end']) ? strip_tags($_POST['end']) : '';

        // form validate
        if (Form::validate($_POST, ['manga', 'house', 'tome', 'next', 'end'])):
            $mangaModel = new MangaModel;
            $mangaModel->setManga($manga)->setHouse($house)->setTome($tome)
                ->setNext($next)->setEnd($end);
            if ($mangaModel->create()):
                header('Location: list'); exit;
            endif;
        endif;

        // form create
        $form = self::mangaForm($manga, $house, $tome, $next, $end);

        // class instance
        $mangaModel = new MangaModel;
        $mangas = $mangaModel->findAllPaginate('end DESC', 18, $id);
        $count = $mangaModel->countPaginate(18);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Manga List '.$id;
        $this->render('manga/list', ['mangaForm' => $form->create(), 'mangas' => $mangas,
            'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /manga/update/{id}
     * @param integer $id
     * @return void
     */
    public function update(int $id): void
    {
        // class instance
        $mangaModel = new MangaModel;
        $mangaFind = $mangaModel->find($id);

        // environment variables
        $manga = isset($_POST['manga']) ? strip_tags($_POST['manga']) : '';
        $house = isset($_POST['house']) ? strip_tags($_POST['house']) : '';
        $tome = isset($_POST['tome']) ? strip_tags($_POST['tome']) : '';
        $next = isset($_POST['next']) ? strip_tags($_POST['next']) : '';
        $end = isset($_POST['end']) ? strip_tags($_POST['end']) : '';

        // form validate
        if (Form::validate($_POST, ['manga', 'house', 'tome', 'next', 'end'])):
            $mangaModel = new MangaModel;
            $mangaModel->setId($mangaFind->id)->setManga($manga)->setHouse($house)->setTome($tome)
                ->setNext($next)->setEnd($end);
            if ($mangaModel->update()):
                header('Location: ../list'); exit;
            endif;
        endif;

        // form create
        $form = self::updateForm($mangaFind->manga, $mangaFind->house, $mangaFind->tome, $mangaFind->next, $mangaFind->end);

        // view
        $this->title = 'LoliSSR | Manga Update';
        $this->render('manga/update', ['updateForm' => $form->create()]);
    }

    /**
     * route /manga/delete/{id}
     * @param integer $id
     * @return void
     */
    public function delete(int $id): void
    {
        // class instance
        $mangaModel = new MangaModel;

        // delete validate
        if ($mangaModel->delete($id)):
            header('Location: ../list'); exit;
        endif;
    }

    /**
     * route /manga/link
     * @return void
     */
    public function link(): void
    {
        // view
        $this->title = 'LoliSSR | Manga Link';
        $this->render('manga/link');
    }

    /**
     * self mangaForm
     * @param string|null $manga
     * @param string|null $house
     * @param string|null $tome
     * @param string|null $next
     * @param string|null $end
     * @return Form
     */
    private static function mangaForm(string $manga = null, string $house = null, string $tome = null,
        string $next = null, string $end = null): Form
    {
        // form
        $form = new Form;
        $form->startForm('post', '#', ['id' => 'mangaForm'])
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'manga',
                    ['placeholder' => 'Manga', 'value' => $manga, 'required' => true, 'autofocus' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'house',
                    ['placeholder' => 'Maison d\'édition', 'value' => $house, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'tome',
                    ['placeholder' => 'Tome', 'value' => $tome, 'required' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'next',
                    ['placeholder' => 'Suivant', 'value' => $next, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center m-b-30'])
                ->startDiv()
                    ->addInput('text', 'end',
                    ['placeholder' => 'Fin (Y, N, ?)', 'value' => $end, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->addButton('Validate', ['type' => 'submit', 'class' => 'link-submit', 'role' => 'button'])
            ->endForm();
        return $form;
    }

    /**
     * self updateForm
     * @param string|null $manga
     * @param string|null $house
     * @param string|null $tome
     * @param string|null $next
     * @param string|null $end
     * @return Form
     */
    private static function updateForm(string $manga = null, string $house = null, string $tome = null,
        string $next = null, string $end = null): Form
    {
        // form
        $form = new Form;
        $form->startForm()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'manga',
                    ['placeholder' => 'Manga', 'value' => $manga, 'required' => true, 'autofocus' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'house',
                    ['placeholder' => 'Maison d\'édition', 'value' => $house, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'tome',
                    ['placeholder' => 'Tome', 'value' => $tome, 'required' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'next',
                    ['placeholder' => 'Suivant', 'value' => $next, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center m-b-30'])
                ->startDiv()
                    ->addInput('text', 'end',
                    ['placeholder' => 'Fin (Y, N, ?)', 'value' => $end, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->addButton('Validate', ['type' => 'submit', 'class' => 'link-submit', 'role' => 'button'])
            ->endForm();
        return $form;
    }
}