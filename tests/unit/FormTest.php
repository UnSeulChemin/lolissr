<?php

declare(strict_types=1);

use App\Core\Form;
use PHPUnit\Framework\TestCase;

final class FormTest extends TestCase
{
    public function testRenderReturnsEmptyStringByDefault(): void
    {
        $form = new Form();

        $this->assertSame('', $form->render());
    }

    public function testStartFormCreatesFormWithDefaultValues(): void
    {
        $form = new Form();

        $result = $form
            ->startForm()
            ->render();

        $this->assertSame('<form action="#" method="post">', $result);
    }

    public function testStartFormUsesProvidedActionAndMethod(): void
    {
        $form = new Form();

        $result = $form
            ->startForm('/manga/ajouter', 'post')
            ->render();

        $this->assertSame('<form action="/manga/ajouter" method="post">', $result);
    }

    public function testStartFormForcesPostWhenMethodIsInvalid(): void
    {
        $form = new Form();

        $result = $form
            ->startForm('/test', 'delete')
            ->render();

        $this->assertSame('<form action="/test" method="post">', $result);
    }

    public function testStartFormAcceptsGetMethod(): void
    {
        $form = new Form();

        $result = $form
            ->startForm('/search', 'get')
            ->render();

        $this->assertSame('<form action="/search" method="get">', $result);
    }

    public function testStartFormEscapesAction(): void
    {
        $form = new Form();

        $result = $form
            ->startForm('/test?x="1"&y=<tag>', 'post')
            ->render();

        $this->assertSame(
            '<form action="/test?x=&quot;1&quot;&amp;y=&lt;tag&gt;" method="post">',
            $result
        );
    }

    public function testStartFormAddsAttributes(): void
    {
        $form = new Form();

        $result = $form
            ->startForm('/upload', 'post', [
                'class' => 'form-layout',
                'id' => 'manga-form',
                'enctype' => 'multipart/form-data',
            ])
            ->render();

        $this->assertSame(
            '<form action="/upload" method="post" class="form-layout" id="manga-form" enctype="multipart/form-data">',
            $result
        );
    }

    public function testEndFormClosesForm(): void
    {
        $form = new Form();

        $result = $form
            ->startForm()
            ->endForm()
            ->render();

        $this->assertSame('<form action="#" method="post"></form>', $result);
    }

    public function testStartDivCreatesDivWithoutAttributes(): void
    {
        $form = new Form();

        $result = $form
            ->startDiv()
            ->render();

        $this->assertSame('<div>', $result);
    }

    public function testStartDivCreatesDivWithAttributes(): void
    {
        $form = new Form();

        $result = $form
            ->startDiv([
                'class' => 'form-group',
                'id' => 'bloc-note',
            ])
            ->render();

        $this->assertSame('<div class="form-group" id="bloc-note">', $result);
    }

    public function testEndDivClosesDiv(): void
    {
        $form = new Form();

        $result = $form
            ->startDiv()
            ->endDiv()
            ->render();

        $this->assertSame('<div></div>', $result);
    }

    public function testAddLabelForCreatesEscapedLabel(): void
    {
        $form = new Form();

        $result = $form
            ->addLabelFor('livre', 'Titre <One Piece>')
            ->render();

        $this->assertSame(
            '<label for="livre">Titre &lt;One Piece&gt;</label>',
            $result
        );
    }

    public function testAddLabelForAddsAttributes(): void
    {
        $form = new Form();

        $result = $form
            ->addLabelFor('slug', 'Slug', [
                'class' => 'form-label',
            ])
            ->render();

        $this->assertSame(
            '<label for="slug" class="form-label">Slug</label>',
            $result
        );
    }

    public function testAddInputCreatesInput(): void
    {
        $form = new Form();

        $result = $form
            ->addInput('text', 'livre')
            ->render();

        $this->assertSame('<input type="text" name="livre">', $result);
    }

    public function testAddInputEscapesTypeNameAndAttributes(): void
    {
        $form = new Form();

        $result = $form
            ->addInput('text', 'livre', [
                'value' => '"One Piece"',
                'placeholder' => '<Titre>',
            ])
            ->render();

        $this->assertSame(
            '<input type="text" name="livre" value="&quot;One Piece&quot;" placeholder="&lt;Titre&gt;">',
            $result
        );
    }

    public function testAddTextareaCreatesTextarea(): void
    {
        $form = new Form();

        $result = $form
            ->addTextarea('commentaire', 'Très bon manga')
            ->render();

        $this->assertSame(
            '<textarea name="commentaire">Très bon manga</textarea>',
            $result
        );
    }

    public function testAddTextareaEscapesValue(): void
    {
        $form = new Form();

        $result = $form
            ->addTextarea('commentaire', '<script>alert("x")</script>')
            ->render();

        $this->assertSame(
            '<textarea name="commentaire">&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;</textarea>',
            $result
        );
    }

    public function testAddSelectCreatesOptions(): void
    {
        $form = new Form();

        $result = $form
            ->addSelect('note', [
                1 => '1',
                2 => '2',
                3 => '3',
            ])
            ->render();

        $this->assertSame(
            '<select name="note"><option value="1">1</option><option value="2">2</option><option value="3">3</option></select>',
            $result
        );
    }

    public function testAddSelectMarksSelectedOption(): void
    {
        $form = new Form();

        $result = $form
            ->addSelect('note', [
                1 => '1',
                2 => '2',
                3 => '3',
            ], [], 2)
            ->render();

        $this->assertSame(
            '<select name="note"><option value="1">1</option><option value="2" selected>2</option><option value="3">3</option></select>',
            $result
        );
    }

    public function testAddSelectUsesStringComparisonForSelectedValue(): void
    {
        $form = new Form();

        $result = $form
            ->addSelect('numero', [
                '01' => 'Tome 1',
                '02' => 'Tome 2',
            ], [], 1)
            ->render();

        $this->assertSame(
            '<select name="numero"><option value="01">Tome 1</option><option value="02">Tome 2</option></select>',
            $result
        );
    }

    public function testAddSelectEscapesOptionValuesAndTexts(): void
    {
        $form = new Form();

        $result = $form
            ->addSelect('test', [
                '<a>' => 'Texte "danger"',
            ])
            ->render();

        $this->assertSame(
            '<select name="test"><option value="&lt;a&gt;">Texte &quot;danger&quot;</option></select>',
            $result
        );
    }

    public function testAddButtonCreatesButton(): void
    {
        $form = new Form();

        $result = $form
            ->addButton('Envoyer')
            ->render();

        $this->assertSame('<button>Envoyer</button>', $result);
    }

    public function testAddButtonEscapesTextAndAddsAttributes(): void
    {
        $form = new Form();

        $result = $form
            ->addButton('<Valider>', [
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ])
            ->render();

        $this->assertSame(
            '<button type="submit" class="btn btn-primary">&lt;Valider&gt;</button>',
            $result
        );
    }

    public function testBooleanAttributesAreRenderedWithoutValueWhenTrue(): void
    {
        $form = new Form();

        $result = $form
            ->addInput('text', 'livre', [
                'required' => true,
                'readonly' => true,
                'disabled' => true,
            ])
            ->render();

        $this->assertSame(
            '<input type="text" name="livre" required readonly disabled>',
            $result
        );
    }

    public function testBooleanAttributesAreIgnoredWhenFalse(): void
    {
        $form = new Form();

        $result = $form
            ->addInput('text', 'livre', [
                'required' => false,
                'readonly' => false,
                'disabled' => false,
            ])
            ->render();

        $this->assertSame('<input type="text" name="livre">', $result);
    }

    public function testNormalAttributesAreEscaped(): void
    {
        $form = new Form();

        $result = $form
            ->addInput('text', 'livre', [
                'data-test' => '"abc" <tag>',
            ])
            ->render();

        $this->assertSame(
            '<input type="text" name="livre" data-test="&quot;abc&quot; &lt;tag&gt;">',
            $result
        );
    }

    public function testMethodsReturnSelfForChaining(): void
    {
        $form = new Form();

        $result = $form
            ->startForm('/submit', 'post')
            ->startDiv(['class' => 'group'])
            ->addLabelFor('livre', 'Livre')
            ->addInput('text', 'livre', ['required' => true])
            ->endDiv()
            ->addButton('Valider', ['type' => 'submit'])
            ->endForm()
            ->render();

        $this->assertSame(
            '<form action="/submit" method="post"><div class="group"><label for="livre">Livre</label><input type="text" name="livre" required></div><button type="submit">Valider</button></form>',
            $result
        );
    }
}