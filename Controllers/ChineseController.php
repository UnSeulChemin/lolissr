<?php
namespace App\Controllers;

use App\Models\ChineseModel;
use App\Core\Form;

class ChineseController extends Controller
{
    /**
     * route /chinese
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | Chinese';
        $this->render('chinese/index');
    }

    /**
     * route /chinese/vocabulary
     * @return void
     */
    public function vocabulary(): void
    {
        // environment variables
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';

        // form validate
        if (Form::validate($_POST, ['word'])):
            $chineseModel = new ChineseModel;
            $chineseModel->setWord($word);
            if ($chineseModel->create()):
                header('Location: vocabulary'); exit;
            endif;
        endif;
        
        // form create
        $form = self::chineseForm($word);

        // class instance
        $chineseModel = new ChineseModel;
        $chineses = $chineseModel->findAll();

        // view
        $this->title = 'LoliSSR | Chinese Vocabulary';
        $this->render('chinese/vocabulary', ['chineseForm' => $form->create(), 'chineses' => $chineses]);
    }

    /**
     * self chineseForm
     * @param string|null $word
     * @return Form
     */
    private static function chineseForm(string $word = null): Form
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