<?php
namespace App\Controllers;

use App\Models\ChineseModel;
use App\Core\Form;
use App\Core\Functions;

class ChineseController extends Controller
{
    /**
     * route /chinese
     */
    public function index(): void
    {
        $this->title = 'LoliSSR | Chinese';
        $this->render('chinese/index');
    }

    /**
     * route /chinese/list
     */
    public function list(): void
    {
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';
        $type = isset($_POST['type']) ? strip_tags($_POST['type']) : '';
        $french = isset($_POST['french']) ? strip_tags($_POST['french']) : '';
        $english = isset($_POST['english']) ? strip_tags($_POST['english']) : '';
        $example = isset($_POST['example']) ? strip_tags($_POST['example']) : '';

        if (Form::validate($_POST, ['word', 'type', 'french', 'english', 'example'])) {
            $chineseModel = new ChineseModel;
            $chineseModel
                ->setWord($word)
                ->setType($type)
                ->setFrench($french)
                ->setEnglish($english)
                ->setExample($example);

            if ($chineseModel->create()) {
                header('Location: ' . Functions::basePath() . 'chinese/list');
                exit;
            }
        }

        $form = self::chineseForm($word, $type, $french, $english, $example);

        $chineseModel = new ChineseModel;
        $chineses = $chineseModel->findAllPaginate('id DESC', 18, 1);
        $count = $chineseModel->countPaginate(18);

        $pathRedirect = Functions::basePath();

        $this->title = 'LoliSSR | Chinese List';
        $this->render('chinese/list', [
            'chineseForm' => $form->create(),
            'chineses' => $chineses,
            'count' => $count,
            'pathRedirect' => $pathRedirect
        ]);
    }

    /**
     * route /chinese/page/{id}
     */
    public function page(int $id): void
    {
        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';
        $type = isset($_POST['type']) ? strip_tags($_POST['type']) : '';
        $french = isset($_POST['french']) ? strip_tags($_POST['french']) : '';
        $english = isset($_POST['english']) ? strip_tags($_POST['english']) : '';
        $example = isset($_POST['example']) ? strip_tags($_POST['example']) : '';

        if (Form::validate($_POST, ['word', 'type', 'french', 'english', 'example'])) {
            $chineseModel = new ChineseModel;
            $chineseModel
                ->setWord($word)
                ->setType($type)
                ->setFrench($french)
                ->setEnglish($english)
                ->setExample($example);

            if ($chineseModel->create()) {
                header('Location: ' . Functions::basePath() . 'chinese/list');
                exit;
            }
        }

        $form = self::chineseForm($word, $type, $french, $english, $example);

        $chineseModel = new ChineseModel;
        $chineses = $chineseModel->findAllPaginate('id DESC', 18, $id);
        $count = $chineseModel->countPaginate(18);

        $pathRedirect = Functions::basePath();

        $this->title = 'LoliSSR | Chinese List ' . $id;
        $this->render('chinese/list', [
            'chineseForm' => $form->create(),
            'chineses' => $chineses,
            'count' => $count,
            'pathRedirect' => $pathRedirect
        ]);
    }

    /**
     * route /chinese/update/{id}
     */
    public function update(int $id): void
    {
        $chineseModel = new ChineseModel;
        $chinese = $chineseModel->find($id);

        if (!$chinese) {
            http_response_code(404);
            exit('Mot introuvable');
        }

        $word = isset($_POST['word']) ? strip_tags($_POST['word']) : '';
        $type = isset($_POST['type']) ? strip_tags($_POST['type']) : '';
        $french = isset($_POST['french']) ? strip_tags($_POST['french']) : '';
        $english = isset($_POST['english']) ? strip_tags($_POST['english']) : '';
        $example = isset($_POST['example']) ? strip_tags($_POST['example']) : '';

        if (Form::validate($_POST, ['word', 'type', 'french', 'english', 'example'])) {
            $chineseModel = new ChineseModel;
            $chineseModel
                ->setId((int) $chinese->id)
                ->setWord($word)
                ->setType($type)
                ->setFrench($french)
                ->setEnglish($english)
                ->setExample($example);

            if ($chineseModel->update()) {
                header('Location: ' . Functions::basePath() . 'chinese/list');
                exit;
            }
        }

        $form = self::updateForm(
            $chinese->word,
            $chinese->type,
            $chinese->french,
            $chinese->english,
            $chinese->example
        );

        $this->title = 'LoliSSR | Chinese Update';
        $this->render('chinese/update', [
            'updateForm' => $form->create()
        ]);
    }

    /**
     * route /chinese/delete/{id}
     */
    public function delete(int $id): void
    {
        $chineseModel = new ChineseModel;

        if ($chineseModel->delete($id)) {
            header('Location: ' . Functions::basePath() . 'chinese/list');
            exit;
        }

        http_response_code(404);
        exit('Suppression impossible');
    }

    /**
     * route /chinese/link
     */
    public function link(): void
    {
        $this->title = 'LoliSSR | Chinese Link';
        $this->render('chinese/link');
    }

    private static function chineseForm(
        ?string $word = null,
        ?string $type = null,
        ?string $french = null,
        ?string $english = null,
        ?string $example = null
    ): Form {
        $form = new Form;
        $form->startForm('post', '#', ['id' => 'chineseForm'])
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'word', [
                        'placeholder' => 'Mot',
                        'value' => $word,
                        'required' => true,
                        'autofocus' => true
                    ])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'type', [
                        'placeholder' => 'Type',
                        'value' => $type,
                        'required' => true
                    ])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'french', [
                        'placeholder' => 'Traduction FR',
                        'value' => $french,
                        'required' => true
                    ])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'english', [
                        'placeholder' => 'Traduction EN',
                        'value' => $english,
                        'required' => true
                    ])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center m-b-30'])
                ->startDiv()
                    ->addInput('text', 'example', [
                        'placeholder' => 'Exemple',
                        'value' => $example,
                        'required' => true
                    ])
                ->endDiv()
            ->endDiv()
            ->addButton('Validate', [
                'type' => 'submit',
                'class' => 'link-submit',
                'role' => 'button'
            ])
            ->endForm();

        return $form;
    }

    private static function updateForm(
        ?string $word = null,
        ?string $type = null,
        ?string $french = null,
        ?string $english = null,
        ?string $example = null
    ): Form {
        $form = new Form;
        $form->startForm('post', '#')
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'word', [
                        'placeholder' => 'Mot',
                        'value' => $word,
                        'required' => true,
                        'autofocus' => true
                    ])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'type', [
                        'placeholder' => 'Type',
                        'value' => $type,
                        'required' => true
                    ])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center-gap-50 m-b-30'])
                ->startDiv()
                    ->addInput('text', 'french', [
                        'placeholder' => 'Traduction FR',
                        'value' => $french,
                        'required' => true
                    ])
                ->endDiv()
                ->startDiv()
                    ->addInput('text', 'english', [
                        'placeholder' => 'Traduction EN',
                        'value' => $english,
                        'required' => true
                    ])
                ->endDiv()
            ->endDiv()
            ->startDiv(['class' => 'flex-center-center m-b-30'])
                ->startDiv()
                    ->addInput('text', 'example', [
                        'placeholder' => 'Exemple',
                        'value' => $example,
                        'required' => true
                    ])
                ->endDiv()
            ->endDiv()
            ->addButton('Validate', [
                'type' => 'submit',
                'class' => 'link-submit',
                'role' => 'button'
            ])
            ->endForm();

        return $form;
    }
}