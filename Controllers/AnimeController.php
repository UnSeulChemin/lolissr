<?php
namespace App\Controllers;

use App\Models\AnimeModel;
use App\Core\Form;
use App\Core\Functions;

class AnimeController extends Controller
{
    /**
     * route /anime
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | Anime';
        $this->render('anime/index');
    }

    /**
     * route /anime/list
     * @return void
     */
    public function list(): void
    {
        // environment variables
        $anime = isset($_POST['anime']) ? strip_tags($_POST['anime']) : '';
        $origin = isset($_POST['origin']) ? strip_tags($_POST['origin']) : '';
        $season = isset($_POST['season']) ? strip_tags($_POST['season']) : '';
        $episode = isset($_POST['episode']) ? strip_tags($_POST['episode']) : '';
        $end = isset($_POST['end']) ? strip_tags($_POST['end']) : '';
        $note = isset($_POST['note']) ? strip_tags($_POST['note']) : '';

        // form validate
        if (Form::validate($_POST, ['anime', 'origin', 'season', 'episode', 'end', 'note'])):
            $animeModel = new AnimeModel;
            $animeModel->setAnime($anime)->setOrigin($origin)->setSeason($season)
                ->setEpisode($episode)->setEnd($end)->setNote($note);
            if ($animeModel->create()):
                header('Location: list'); exit;
            endif;
        endif;

        // form create
        $form = self::animeForm($anime, $origin, $season, $episode, $end, $note);

        // class instance
        $animeModel = new AnimeModel;
        $animes = $animeModel->findAllPaginate('end DESC', 18, 1);
        $count = $animeModel->countPaginate(18);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Anime List';
        $this->render('anime/list', ['animeForm' => $form->create(), 'animes' => $animes,
            'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /anime/page/{id}
     * @param int $id
     * @return void
     */
    public function page(int $id): void
    {
        // environment variables
        $anime = isset($_POST['anime']) ? strip_tags($_POST['anime']) : '';
        $origin = isset($_POST['origin']) ? strip_tags($_POST['origin']) : '';
        $season = isset($_POST['season']) ? strip_tags($_POST['season']) : '';
        $episode = isset($_POST['episode']) ? strip_tags($_POST['episode']) : '';
        $end = isset($_POST['end']) ? strip_tags($_POST['end']) : '';
        $note = isset($_POST['note']) ? strip_tags($_POST['note']) : '';

        // form validate
        if (Form::validate($_POST, ['anime', 'origin', 'season', 'episode', 'end', 'note'])):
            $animeModel = new AnimeModel;
            $animeModel->setAnime($anime)->setOrigin($origin)->setSeason($season)
                ->setEpisode($episode)->setEnd($end)->setNote($note);
            if ($animeModel->create()):
                header('Location: list'); exit;
            endif;
        endif;

        // form create
        $form = self::animeForm($anime, $origin, $season, $episode, $end, $note);

        // class instance
        $animeModel = new AnimeModel;
        $animes = $animeModel->findAllPaginate('end DESC', 18, $id);
        $count = $animeModel->countPaginate(18);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Anime List '.$id;
        $this->render('anime/list', ['animeForm' => $form->create(), 'animes' => $animes,
            'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /anime/update/{id}
     * @param integer $id
     * @return void
     */
    public function update(int $id): void
    {
        // class instance
        $animeModel = new AnimeModel;
        $animeFind = $animeModel->find($id);

        // environment variables
        $anime = isset($_POST['anime']) ? strip_tags($_POST['anime']) : '';
        $origin = isset($_POST['origin']) ? strip_tags($_POST['origin']) : '';
        $season = isset($_POST['season']) ? strip_tags($_POST['season']) : '';
        $episode = isset($_POST['episode']) ? strip_tags($_POST['episode']) : '';
        $end = isset($_POST['end']) ? strip_tags($_POST['end']) : '';
        $note = isset($_POST['note']) ? strip_tags($_POST['note']) : '';

        // form validate
        if (Form::validate($_POST, ['anime', 'origin', 'season', 'episode', 'end', 'note'])):
            $animeModel = new AnimeModel;
            $animeModel->setId($animeFind->id)->setAnime($anime)->setOrigin($origin)->setSeason($season)
                ->setEpisode($episode)->setEnd($end)->setNote($note);
            if ($animeModel->update()):
                header('Location: ../list'); exit;
            endif;
        endif;

        // form create
        $form = self::updateForm($animeFind->anime, $animeFind->origin, $animeFind->season,
            $animeFind->episode, $animeFind->end, $animeFind->note);

        // view
        $this->title = 'LoliSSR | Anime Update';
        $this->render('anime/update', ['updateForm' => $form->create()]);
    }

    /**
     * route /anime/delete/{id}
     * @param integer $id
     * @return void
     */
    public function delete(int $id): void
    {
        // class instance
        $animeModel = new AnimeModel;

        // delete validate
        if ($animeModel->delete($id)):
            header('Location: ../list'); exit;
        endif;
    }

    /**
     * route /anime/link
     * @return void
     */
    public function link(): void
    {
        // view
        $this->title = 'LoliSSR | Anime Link';
        $this->render('anime/link');
    }

    /**
     * self animeForm
     * @param string|null $anime
     * @param string|null $origin
     * @param string|null $season
     * @param string|null $episode
     * @param string|null $end
     * @param string|null $note
     * @return Form
     */
    private static function animeForm(string $anime = null, string $origin = null, string $season = null,
        string $episode = null, string $end = null, string $note = null): Form
    {
        // form
        $form = new Form;
        $form->startForm('post', '#', ['id' => 'animeForm'])
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'anime',
                    ['placeholder' => 'Anime', 'value' => $anime, 'required' => true, 'autofocus' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'origin',
                    ['placeholder' => 'Origine', 'value' => $origin, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'season',
                    ['placeholder' => 'Saison', 'value' => $season, 'required' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'episode',
                    ['placeholder' => 'Épisode', 'value' => $episode, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'end',
                    ['placeholder' => 'Fin (Y, N, ?)', 'value' => $end, 'required' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'note',
                    ['placeholder' => 'Note (1, 2, 3, 4 ,5)', 'value' => $note, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->addButton('Validate', ['type' => 'submit', 'class' => 'link-submit', 'role' => 'button'])
            ->endForm();
        return $form;
    }

    /**
     * self updateForm
     * @param string|null $anime
     * @param string|null $origin
     * @param string|null $season
     * @param string|null $episode
     * @param string|null $end
     * @param string|null $note
     * @return Form
     */
    private static function updateForm(string $anime = null, string $origin = null, string $season = null,
        string $episode = null, string $end = null, string $note = null): Form
    {
        // form
        $form = new Form;
        $form->startForm()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'anime',
                    ['placeholder' => 'Anime', 'value' => $anime, 'required' => true, 'autofocus' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'origin',
                    ['placeholder' => 'Origine', 'value' => $origin, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'season',
                    ['placeholder' => 'Saison', 'value' => $season, 'required' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'episode',
                    ['placeholder' => 'Épisode', 'value' => $episode, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'end',
                    ['placeholder' => 'Fin (Y, N, ?)', 'value' => $end, 'required' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'note',
                    ['placeholder' => 'Note (1, 2, 3, 4 ,5)', 'value' => $note, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->addButton('Validate', ['type' => 'submit', 'class' => 'link-submit', 'role' => 'button'])
            ->endForm();
        return $form;
    }
}