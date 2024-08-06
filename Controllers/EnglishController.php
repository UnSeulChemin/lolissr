<?php
namespace App\Controllers;

use App\Models\EnglishModel;
use App\Core\Form;

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
     * route /english/vocabulary
     * @return void
     */
    public function vocabulary(): void
    {
        // environment variables
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';

        // form validate
        if (Form::validate($_POST, ['word'])):
            $englishModel = new EnglishModel;
            $englishModel->setWord($word);
            if ($englishModel->create()):
                header('Location: vocabulary'); exit;
            endif;
        endif;
        
        // form create
        $form = self::englishForm($word);

        // class instance
        $englishModel = new EnglishModel;
        $englishs = $englishModel->findAll();

        // view
        $this->title = 'LoliSSR | English | Vocabulary';
        $this->render('english/vocabulary', ['englishForm' => $form->create(), 'englishs' => $englishs]);
    }

    /**
     * self englishForm
     * @param string|null $word
     * @return Form
     */
    private static function englishForm(string $word = null): Form
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