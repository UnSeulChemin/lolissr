<?php
namespace App\Controllers;

use App\Models\FrenchModel;
use App\Core\Form;

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
     * route /french/vocabulary
     * @return void
     */
    public function vocabulary(): void
    {
        // environment variables
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';

        // form validate
        if (Form::validate($_POST, ['word'])):
            $frenchModel = new FrenchModel;
            $frenchModel->setWord($word);
            if ($frenchModel->create()):
                header('Location: vocabulary'); exit;
            endif;
        endif;
        
        // form create
        $form = self::frenchForm($word);

        // class instance
        $frenchModel = new FrenchModel;
        $frenchs = $frenchModel->findAll();

        // view
        $this->title = 'LoliSSR | French | Vocabulary';
        $this->render('french/vocabulary', ['frenchForm' => $form->create(), 'frenchs' => $frenchs]);
    }

    /**
     * self frenchForm
     * @param string|null $word
     * @return Form
     */
    private static function frenchForm(string $word = null): Form
    {
        // form
        $form = new Form;
        $form->startForm()
            ->startDiv()
                ->addInput('text', 'word',
                ['placeholder' => 'Word', 'value' => $word, 'required' => true, 'autofocus' => true])
            ->endDiv()
            ->addButton('Validate', ['type' => 'submit', 'class' => 'link-submit', 'role' => 'button'])
            ->endForm();
        return $form;
    }
}