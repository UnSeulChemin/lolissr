<?php
namespace App\Controllers;

use App\Models\EnglishModel;
use App\Core\Form;
use App\Core\Functions;

class EnglishController extends Controller
{
    /**
     * route /english
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | English';
        $this->render('english/index');
    }

    /**
     * route /english/list
     * @return void
     */
    public function list(): void
    {
        // environment variables
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';
        $type = isset($_POST['type']) ? strip_tags($_POST['type']) : '';
        $french = isset($_POST['french']) ? strip_tags($_POST['french']) : '';
        $example = isset($_POST['example']) ? strip_tags($_POST['example']) : '';

        // form validate
        if (Form::validate($_POST, ['word', 'type', 'french', 'example'])):
            $englishModel = new EnglishModel;
            $englishModel->setWord($word)->setType($type)->setFrench($french)->setExample($example);
            if ($englishModel->create()):
                header('Location: list'); exit;
            endif;
        endif;
        
        // form create
        $form = self::englishForm($word, $type, $french, $example);

        // class instance
        $englishModel = new EnglishModel;
        $englishs = $englishModel->findAllPaginate('id DESC', 18, 1);
        $count = $englishModel->countPaginate(18);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | English List';
        $this->render('english/list', ['englishForm' => $form->create(), 'englishs' => $englishs,
            'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /english/page/{id}
     * @param int $id
     * @return void
     */
    public function page(int $id): void
    {
        // environment variables
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';
        $type = isset($_POST['type']) ? strip_tags($_POST['type']) : '';
        $french = isset($_POST['french']) ? strip_tags($_POST['french']) : '';
        $example = isset($_POST['example']) ? strip_tags($_POST['example']) : '';

        // form validate
        if (Form::validate($_POST, ['word', 'type', 'french', 'example'])):
            $englishModel = new EnglishModel;
            $englishModel->setWord($word)->setType($type)->setFrench($french)->setExample($example);
            if ($englishModel->create()):
                header('Location: list'); exit;
            endif;
        endif;
        
        // form create
        $form = self::englishForm($word, $type, $french, $example);

        // class instance
        $englishModel = new EnglishModel;
        $englishs = $englishModel->findAllPaginate('id DESC', 18, $id);
        $count = $englishModel->countPaginate(18);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | English List '.$id;
        $this->render('english/list', ['englishForm' => $form->create(), 'englishs' => $englishs,
            'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /english/update/{id}
     * @param integer $id
     * @return void
     */
    public function update(int $id): void
    {
        // class instance
        $englishModel = new EnglishModel;
        $english = $englishModel->find($id);

        // environment variables
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';
        $type = isset($_POST['type']) ? strip_tags($_POST['type']) : '';
        $french = isset($_POST['french']) ? strip_tags($_POST['french']) : '';
        $example = isset($_POST['example']) ? strip_tags($_POST['example']) : '';

        // form validate
        if (Form::validate($_POST, ['word', 'type', 'french', 'example'])):
            $englishModel = new EnglishModel;
            $englishModel->setId($english->id)->setWord($word)->setType($type)->setFrench($french)->setExample($example);
            if ($englishModel->update()):
                header('Location: ../list'); exit;
            endif;
        endif;

        // form create
        $form = self::updateForm($english->word, $english->type, $english->french, $english->example);
       
        // view
        $this->title = 'LoliSSR | English Update';
        $this->render('english/update', ['updateForm' => $form->create()]);
    }

    /**
     * route /english/delete/{id}
     * @param integer $id
     * @return void
     */
    public function delete(int $id): void
    {
        // class instance
        $englishModel = new EnglishModel;

        // delete validate
        if ($englishModel->delete($id)):
            header('Location: ../list'); exit;
        endif;
    }

    /**
     * self englishForm
     * @param string|null $word
     * @param string|null $type
     * @param string|null $french
     * @param string|null $example
     * @return Form
     */
    private static function englishForm(string $word = null, string $type = null, string $french = null, 
        string $example = null): Form
    {
        // form
        $form = new Form;
        $form->startForm('post', '#', ['id' => 'englishForm'])
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'word',
                    ['placeholder' => 'Mot', 'value' => $word, 'required' => true, 'autofocus' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'type',
                    ['placeholder' => 'Type', 'value' => $type, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'french',
                    ['placeholder' => 'Traduction FR', 'value' => $french, 'required' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'example',
                    ['placeholder' => 'Exemple', 'value' => $example, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->addButton('Validate', ['type' => 'submit', 'class' => 'link-submit', 'role' => 'button'])
            ->endForm();
        return $form;
    }

    /**
     * self updateForm
     * @param string|null $word
     * @param string|null $type
     * @param string|null $french
     * @param string|null $example
     * @return Form
     */
    private static function updateForm(string $word = null, string $type = null, string $french = null, 
        string $example = null): Form
    {
        // form
        $form = new Form;
        $form->startForm()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'word',
                    ['placeholder' => 'Mot', 'value' => $word, 'required' => true, 'autofocus' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'type',
                    ['placeholder' => 'Type', 'value' => $type, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'french',
                    ['placeholder' => 'Traduction FR', 'value' => $french, 'required' => true])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'example',
                    ['placeholder' => 'Exemple', 'value' => $example, 'required' => true])
                ->endDiv()
            ->endDiv()
            ->addButton('Validate', ['type' => 'submit', 'class' => 'link-submit', 'role' => 'button'])
            ->endForm();
        return $form;
    }
}