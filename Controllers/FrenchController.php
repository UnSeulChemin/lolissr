<?php
namespace App\Controllers;

use App\Models\FrenchModel;
use App\Core\Form;
use App\Core\Functions;

class FrenchController extends Controller
{
    /**
     * route /french
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | French';
        $this->render('french/index');
    }

    /**
     * route /french/list
     * @return void
     */
    public function list(): void
    {
        // environment variables
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';
        $type = isset($_POST['type']) ? strip_tags($_POST['type']) : '';
        $definition = isset($_POST['definition']) ? strip_tags($_POST['definition']) : '';
        $example = isset($_POST['example']) ? strip_tags($_POST['example']) : '';

        // form validate
        if (Form::validate($_POST, ['word', 'type', 'definition', 'example'])):
            $frenchModel = new FrenchModel;
            $frenchModel->setWord($word)->setType($type)->setDefinition($definition)->setExample($example);
            if ($frenchModel->create()):
                header('Location: list'); exit;
            endif;
        endif;
        
        // form create
        $form = self::frenchForm($word, $type, $definition, $example);

        // class instance
        $frenchModel = new FrenchModel;
        $frenchs = $frenchModel->findAllPaginate('id DESC', 18, 1);
        $count = $frenchModel->countPaginate(18);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | French List';
        $this->render('french/list', ['frenchForm' => $form->create(), 'frenchs' => $frenchs,
            'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /french/page/{id}
     * @param int $id
     * @return void
     */
    public function page(int $id): void
    {
        // environment variables
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';
        $type = isset($_POST['type']) ? strip_tags($_POST['type']) : '';
        $definition = isset($_POST['definition']) ? strip_tags($_POST['definition']) : '';
        $example = isset($_POST['example']) ? strip_tags($_POST['example']) : '';

        // form validate
        if (Form::validate($_POST, ['word', 'type', 'definition', 'example'])):
            $frenchModel = new FrenchModel;
            $frenchModel->setWord($word)->setType($type)->setDefinition($definition)->setExample($example);
            if ($frenchModel->create()):
                header('Location: list'); exit;
            endif;
        endif;
        
        // form create
        $form = self::frenchForm($word, $type, $definition, $example);

        // class instance
        $frenchModel = new FrenchModel;
        $frenchs = $frenchModel->findAllPaginate('id DESC', 18, $id);
        $count = $frenchModel->countPaginate(18);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | French List '.$id;
        $this->render('french/list', ['frenchForm' => $form->create(), 'frenchs' => $frenchs,
            'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /french/update/{id}
     * @param integer $id
     * @return void
     */
    public function update(int $id): void
    {
        // class instance
        $frenchModel = new FrenchModel;
        $french = $frenchModel->find($id);

        // environment variables
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';
        $type = isset($_POST['type']) ? strip_tags($_POST['type']) : '';
        $definition = isset($_POST['definition']) ? strip_tags($_POST['definition']) : '';
        $example = isset($_POST['example']) ? strip_tags($_POST['example']) : '';

        // form validate
        if (Form::validate($_POST, ['word', 'type', 'definition', 'example'])):
            $frenchModel = new FrenchModel;
            $frenchModel->setId($french->id)->setWord($word)->setType($type)->setDefinition($definition)->setExample($example);
            if ($frenchModel->update()):
                header('Location: ../list'); exit;
            endif;
        endif;

        // form create
        $form = self::updateForm($french->word, $french->type, $french->definition, $french->example);
       
        // view
        $this->title = 'LoliSSR | French Update';
        $this->render('french/update', ['updateForm' => $form->create()]);
    }

    /**
     * route /french/delete/{id}
     * @param integer $id
     * @return void
     */
    public function delete(int $id): void
    {
        // class instance
        $frenchModel = new FrenchModel;

        // delete validate
        if ($frenchModel->delete($id)):
            header('Location: ../list'); exit;
        endif;
    }

    /**
     * self frenchForm
     * @param string|null $word
     * @param string|null $type
     * @param string|null $definition
     * @param string|null $example
     * @return Form
     */
    private static function frenchForm(string $word = null, string $type = null, string $definition = null,
        string $example = null): Form
    {
        // form
        $form = new Form;
        $form->startForm('post', '#', ['id' => 'frenchForm'])
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
                    ->addInput('text', 'definition',
                    ['placeholder' => 'Définition', 'value' => $definition, 'required' => true])
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
     * @param string|null $definition
     * @param string|null $example
     * @return Form
     */
    private static function updateForm(string $word = null, string $type = null, string $definition = null,
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
                    ->addInput('text', 'definition',
                    ['placeholder' => 'Définition', 'value' => $definition, 'required' => true])
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