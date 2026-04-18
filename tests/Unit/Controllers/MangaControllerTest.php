<?php

declare(strict_types=1);

use App\Controllers\MangaController;
use App\Models\MangaModel;
use PHPUnit\Framework\TestCase;
use App\Core\Validator;

final class MangaControllerTest extends TestCase
{
    public function testNormalizePostedNoteReturnsNullWhenValueIsNull(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $this->assertNull($controller->callNormalizePostedNote(null));
    }

    public function testNormalizePostedNoteReturnsNullWhenValueIsEmpty(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $this->assertNull($controller->callNormalizePostedNote(''));
        $this->assertNull($controller->callNormalizePostedNote('   '));
    }

    public function testNormalizePostedNoteReturnsNullWhenValueIsOutOfRange(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $this->assertNull($controller->callNormalizePostedNote('0'));
        $this->assertNull($controller->callNormalizePostedNote('6'));
        $this->assertNull($controller->callNormalizePostedNote('-1'));
    }

    public function testNormalizePostedNoteReturnsIntegerWhenValueIsValid(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $this->assertSame(1, $controller->callNormalizePostedNote('1'));
        $this->assertSame(3, $controller->callNormalizePostedNote('3'));
        $this->assertSame(5, $controller->callNormalizePostedNote('5'));
    }

    public function testCollectionCallsNotFoundWhenPageIsNotNumeric(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->collection('abc');

        $this->assertSame('Page introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testCollectionCallsNotFoundWhenPageIsLowerThanOne(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->collection('0');

        $this->assertSame('Page introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testCollectionCallsNotFoundWhenPageIsGreaterThanLastPage(): void
    {
        $model = new FakeMangaModel();
        $model->countFirstTomesPaginateReturn = 3;

        $controller = new TestableMangaController($model);
        $controller->collection('4');

        $this->assertSame('Page introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testCollectionRendersExpectedViewForValidPage(): void
    {
        $mangas = [
            (object) [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece'
            ]
        ];

        $model = new FakeMangaModel();
        $model->countFirstTomesPaginateReturn = 3;
        $model->findAllFirstTomesReturn = $mangas;

        $controller = new TestableMangaController($model);
        $controller->collection('2');

        $this->assertNull($controller->notFoundMessage);
        $this->assertSame('manga/collection', $controller->renderedView);
        $this->assertSame('Manga | Collection - Page 2', $controller->exposedTitle());

        $this->assertSame(2, $controller->renderedData['currentPage']);
        $this->assertSame(3, $controller->renderedData['compteur']);
        $this->assertNull($controller->renderedData['slugFilter']);
        $this->assertCount(1, $controller->renderedData['mangas']);
    }

    public function testCollectionAjaxCallsNotFoundWhenPageIsNotNumeric(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->collectionAjax('abc');

        $this->assertSame('Page introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testCollectionAjaxCallsNotFoundWhenPageIsLowerThanOne(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->collectionAjax('0');

        $this->assertSame('Page introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testCollectionAjaxCallsNotFoundWhenPageIsGreaterThanLastPage(): void
    {
        $model = new FakeMangaModel();
        $model->countFirstTomesPaginateReturn = 2;

        $controller = new TestableMangaController($model);
        $controller->collectionAjax('3');

        $this->assertSame('Page introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testCollectionAjaxRendersExpectedPartialForValidPage(): void
    {
        $mangas = [
            (object) [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece'
            ]
        ];

        $model = new FakeMangaModel();
        $model->countFirstTomesPaginateReturn = 3;
        $model->findAllFirstTomesReturn = $mangas;

        $controller = new TestableMangaController($model);
        $controller->collectionAjax('2');

        $this->assertNull($controller->notFoundMessage);
        $this->assertSame('manga/partials/collection_ajax', $controller->renderedView);
        $this->assertSame(2, $controller->renderedData['currentPage']);
        $this->assertSame(3, $controller->renderedData['compteur']);
        $this->assertNull($controller->renderedData['slugFilter']);
        $this->assertCount(1, $controller->renderedData['mangas']);
    }

    public function testRechercheRendersEmptyResultsWhenQueryIsEmpty(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->recherche('');

        $this->assertSame('Manga | Recherche', $controller->exposedTitle());
        $this->assertSame('manga/search', $controller->renderedView);
        $this->assertSame([], $controller->renderedData['mangas']);
        $this->assertSame('', $controller->renderedData['search']);
    }

    public function testRechercheRendersResultsWhenQueryIsProvided(): void
    {
        $results = [
            (object) [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece'
            ]
        ];

        $model = new FakeMangaModel();
        $model->searchMangasReturn = $results;

        $controller = new TestableMangaController($model);
        $controller->recherche('one-piece');

        $this->assertSame('Manga | Recherche : one piece', $controller->exposedTitle());
        $this->assertSame('manga/search', $controller->renderedView);
        $this->assertSame($results, $controller->renderedData['mangas']);
        $this->assertSame('one piece', $controller->renderedData['search']);
    }

    public function testSerieCallsNotFoundWhenSlugDoesNotExist(): void
    {
        $model = new FakeMangaModel();
        $model->findBySlugReturn = [];

        $controller = new TestableMangaController($model);
        $controller->serie('unknown-slug');

        $this->assertSame('Manga introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testSerieRendersCollectionWhenSlugExists(): void
    {
        $mangas = [
            (object) [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece'
            ],
            (object) [
                'slug' => 'one-piece',
                'numero' => 2,
                'livre' => 'One Piece'
            ]
        ];

        $model = new FakeMangaModel();
        $model->findBySlugReturn = $mangas;

        $controller = new TestableMangaController($model);
        $controller->serie('one-piece');

        $this->assertNull($controller->notFoundMessage);
        $this->assertSame('Manga | One Piece', $controller->exposedTitle());
        $this->assertSame('manga/collection', $controller->renderedView);
        $this->assertSame($mangas, $controller->renderedData['mangas']);
        $this->assertSame('one-piece', $controller->renderedData['slugFilter']);
        $this->assertNull($controller->renderedData['compteur']);
        $this->assertSame(1, $controller->renderedData['currentPage']);
    }

    public function testShowCallsNotFoundWhenMangaDoesNotExist(): void
    {
        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = false;

        $controller = new TestableMangaController($model);
        $controller->show('one-piece', '20');

        $this->assertSame('Manga introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testShowRendersLivreWhenMangaExists(): void
    {
        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 20,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;

        $controller = new TestableMangaController($model);
        $controller->show('one-piece', '20');

        $this->assertNull($controller->notFoundMessage);
        $this->assertSame('Manga | One Piece', $controller->exposedTitle());
        $this->assertSame('manga/livre', $controller->renderedView);
        $this->assertSame($manga, $controller->renderedData['manga']);
    }

    public function testModifierCallsNotFoundWhenMangaDoesNotExist(): void
    {
        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = false;

        $controller = new TestableMangaController($model);
        $controller->modifier('one-piece', '20');

        $this->assertSame('Manga introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testModifierRendersEditWhenMangaExists(): void
    {
        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 20,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;

        $controller = new TestableMangaController($model);
        $controller->modifier('one-piece', '20');

        $this->assertNull($controller->notFoundMessage);
        $this->assertSame('Manga | Modifier', $controller->exposedTitle());
        $this->assertSame('manga/edit', $controller->renderedView);
        $this->assertSame($manga, $controller->renderedData['manga']);
    }

    public function testIndexRendersMangaIndex(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->index();

        $this->assertSame('Manga', $controller->exposedTitle());
        $this->assertSame('manga/index', $controller->renderedView);
    }

    public function testLienRendersLienView(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->lien();

        $this->assertSame('Manga | Lien', $controller->exposedTitle());
        $this->assertSame('manga/lien', $controller->renderedView);
    }

    public function testAjouterRendersAjouterView(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->ajouter();

        $this->assertSame('Manga | Ajouter', $controller->exposedTitle());
        $this->assertSame('manga/ajouter', $controller->renderedView);
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
        $_SERVER = [];
    }

    public function testUpdateCallsMethodNotAllowedWhenRequestMethodIsNotPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->update('one-piece', '1');

        $this->assertSame(
            'Méthode non autorisée pour la modification d’un manga',
            $controller->methodNotAllowedMessage
        );
    }

    public function testUpdateCallsNotFoundWhenMangaDoesNotExist(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = false;

        $controller = new TestableMangaController($model);

        $controller->update('one-piece', '1');

        $this->assertSame('Manga introuvable', $controller->notFoundMessage);
    }

    public function testUpdateRedirectsWithValidationErrorsWhenValidatorFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;

        $validator = new FakeValidator();
        $validator->failsReturn = true;
        $validator->errorsReturn = [
            'commentaire' => 'Le commentaire est invalide.'
        ];

        $controller = new TestableMangaController($model);
        $controller->fakeValidator = $validator;

        $controller->update('one-piece', '1');

        $this->assertSame('manga/update/one-piece/1', $controller->validationErrorPath);
        $this->assertSame(
            ['commentaire' => 'Le commentaire est invalide.'],
            $controller->validationErrorErrors
        );
    }

    public function testUpdateRedirectsWithSuccessWhenUpdateSucceeds(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['jacquette'] = '4';
        $_POST['livre_note'] = '5';
        $_POST['commentaire'] = 'Très bon tome';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;
        $model->updateMangaReturn = true;

        $validator = new FakeValidator();
        $validator->failsReturn = false;

        $controller = new TestableMangaController($model);
        $controller->fakeValidator = $validator;

        $controller->update('one-piece', '1');

        $this->assertSame('manga/one-piece/1', $controller->successRedirectPath);
        $this->assertSame('Manga mis à jour avec succès', $controller->successRedirectMessage);

        $this->assertSame('one-piece', $model->updateMangaArgs['slug']);
        $this->assertSame(1, $model->updateMangaArgs['numero']);
        $this->assertSame(4, $model->updateMangaArgs['jacquette']);
        $this->assertSame(5, $model->updateMangaArgs['livre_note']);
        $this->assertSame('Très bon tome', $model->updateMangaArgs['commentaire']);
    }

    public function testAjaxUpdateNoteReturns405WhenRequestMethodIsNotPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->ajaxUpdateNote('one-piece', '1');

        $this->assertSame(405, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('Méthode non autorisée', $controller->jsonData['message']);
    }

    public function testAjaxUpdateNoteReturns404WhenMangaDoesNotExist(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = false;

        $controller = new TestableMangaController($model);

        $controller->ajaxUpdateNote('one-piece', '1');

        $this->assertSame(404, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('Manga introuvable', $controller->jsonData['message']);
    }

    public function testAjaxUpdateNoteReturns422WhenValidatorFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;

        $validator = new FakeValidator();
        $validator->failsReturn = true;
        $validator->errorsReturn = [
            'jacquette' => 'La note jacquette doit être un entier.'
        ];

        $controller = new TestableMangaController($model);
        $controller->fakeValidator = $validator;

        $controller->ajaxUpdateNote('one-piece', '1');

        $this->assertSame(422, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame(
            'La note jacquette doit être un entier.',
            $controller->jsonData['message']
        );
        $this->assertSame(
            ['jacquette' => 'La note jacquette doit être un entier.'],
            $controller->jsonData['errors']
        );
    }

    public function testAjaxUpdateNoteReturnsSuccessWhenUpdateSucceeds(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['jacquette'] = '2';
        $_POST['livre_note'] = '4';
        $_POST['commentaire'] = 'Correct';

        $existing = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece'
        ];

        $fresh = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'jacquette' => 2,
            'livre_note' => 4,
            'note' => 6
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroSequence = [$existing, $fresh];
        $model->updateMangaReturn = true;

        $validator = new FakeValidator();
        $validator->failsReturn = false;

        $controller = new TestableMangaController($model);
        $controller->fakeValidator = $validator;

        $controller->ajaxUpdateNote('one-piece', '1');

        $this->assertSame(200, $controller->jsonStatus);
        $this->assertTrue($controller->jsonData['success']);
        $this->assertSame('Notes mises à jour', $controller->jsonData['message']);
        $this->assertSame(2, $controller->jsonData['jacquette']);
        $this->assertSame(4, $controller->jsonData['livre_note']);
        $this->assertSame(6, $controller->jsonData['note']);
    }

    public function testAjouterTraitementCallsMethodNotAllowedWhenRequestMethodIsNotPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->ajouterTraitement();

        $this->assertSame(
            'Méthode non autorisée pour l’ajout d’un manga',
            $controller->methodNotAllowedMessage
        );
    }

    public function testAjouterTraitementRedirectsWithValidationErrorsWhenValidatorFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $validator = new FakeValidator();
        $validator->failsReturn = true;
        $validator->errorsReturn = [
            'livre' => 'Le titre est obligatoire.'
        ];

        $controller = new TestableMangaController(new FakeMangaModel());
        $controller->fakeValidator = $validator;

        $controller->ajouterTraitement();

        $this->assertSame('manga/ajouter', $controller->validationErrorPath);
        $this->assertSame(
            ['livre' => 'Le titre est obligatoire.'],
            $controller->validationErrorErrors
        );
    }

    public function testAjouterTraitementRedirectsWithErrorWhenMangaAlreadyExists(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['livre'] = 'One Piece';
        $_POST['slug'] = 'one-piece';
        $_POST['numero'] = '1';
        $_POST['commentaire'] = 'Test';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;

        $validator = new FakeValidator();
        $validator->failsReturn = false;

        $controller = new TestableMangaController($model);
        $controller->fakeValidator = $validator;
        $controller->testUploadMode = false;

        $controller->ajouterTraitement();

        $this->assertSame('manga/ajouter', $controller->errorRedirectPath);
        $this->assertSame('Ce manga existe déjà', $controller->errorRedirectMessage);
    }

    public function testAjouterTraitementRedirectsWithSuccessInTestUploadMode(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['livre'] = 'One Piece';
        $_POST['slug'] = 'one-piece';
        $_POST['numero'] = '1';
        $_POST['commentaire'] = 'Test';

        $validator = new FakeValidator();
        $validator->failsReturn = false;

        $controller = new TestableMangaController(new FakeMangaModel());
        $controller->fakeValidator = $validator;
        $controller->testUploadMode = true;

        $controller->ajouterTraitement();

        $this->assertSame('manga/ajouter', $controller->successRedirectPath);
        $this->assertSame(
            'Upload test OK (aucune écriture en base)',
            $controller->successRedirectMessage
        );
    }

    public function testSerieRedirectsWhenSlugIsNotCanonical(): void
    {
        $mangas = [
            (object) [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece'
            ]
        ];

        $model = new FakeMangaModel();
        $model->findBySlugReturn = $mangas;

        $controller = new TestableMangaController($model);
        $controller->serie('One Piece');

        $this->assertSame('manga/serie/one-piece', $controller->successRedirectPath);
    }

    public function testShowRedirectsWhenSlugIsNotCanonical(): void
    {
        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 20,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;

        $controller = new TestableMangaController($model);
        $controller->show('One Piece', '20');

        $this->assertSame('manga/one-piece/20', $controller->successRedirectPath);
    }

    public function testModifierRedirectsWhenSlugIsNotCanonical(): void
    {
        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 20,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;

        $controller = new TestableMangaController($model);
        $controller->modifier('One Piece', '20');

        $this->assertSame('manga/update/one-piece/20', $controller->successRedirectPath);
    }

    public function testSearchAjaxReturnsEmptyArrayWhenQueryIsEmpty(): void
    {
        $controller = new TestableMangaController(new FakeMangaModel());

        $controller->searchAjax('');

        $this->assertSame(200, $controller->jsonStatus);
        $this->assertSame([], $controller->jsonData);
    }

    public function testSearchAjaxReturnsLimitedFormattedResults(): void
    {
        $results = [
            (object) [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece',
                'thumbnail' => 'one-piece-01',
                'extension' => 'jpg',
                'note' => 9
            ],
            (object) [
                'slug' => 'naruto',
                'numero' => 2,
                'livre' => 'Naruto',
                'thumbnail' => 'naruto-02',
                'extension' => 'png',
                'note' => 8
            ]
        ];

        $model = new FakeMangaModel();
        $model->searchMangasReturn = $results;

        $controller = new TestableMangaController($model);
        $controller->searchAjax('one-piece');

        $this->assertSame(200, $controller->jsonStatus);
        $this->assertCount(2, $controller->jsonData);

        $this->assertSame('one-piece', $controller->jsonData[0]['slug']);
        $this->assertSame(1, $controller->jsonData[0]['numero']);
        $this->assertSame('One Piece', $controller->jsonData[0]['livre']);
        $this->assertSame('one-piece-01', $controller->jsonData[0]['thumbnail']);
        $this->assertSame('jpg', $controller->jsonData[0]['extension']);
        $this->assertSame(9, $controller->jsonData[0]['note']);
    }

    public function testUpdateReturns409JsonWhenSlugIsNotCanonicalInAjax(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;

        $controller = new TestableMangaController($model);
        $controller->ajaxRequest = true;

        $controller->update('One Piece', '1');

        $this->assertSame(409, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('URL non canonique', $controller->jsonData['message']);
        $this->assertSame('/lolissr/manga/update/one-piece/1', $controller->jsonData['redirect']);
    }

    public function testUpdateRedirectsWithErrorWhenUpdateFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['jacquette'] = '4';
        $_POST['livre_note'] = '5';
        $_POST['commentaire'] = 'Très bon tome';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;
        $model->updateMangaReturn = false;

        $validator = new FakeValidator();
        $validator->failsReturn = false;

        $controller = new TestableMangaController($model);
        $controller->fakeValidator = $validator;

        $controller->update('one-piece', '1');

        $this->assertSame('manga/update/one-piece/1', $controller->errorRedirectPath);
        $this->assertSame('Erreur lors de la mise à jour', $controller->errorRedirectMessage);
    }

    public function testAjaxUpdateNoteReturns409WhenSlugIsNotCanonical(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;

        $controller = new TestableMangaController($model);
        $controller->ajaxUpdateNote('One Piece', '1');

        $this->assertSame(409, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('URL non canonique', $controller->jsonData['message']);
        $this->assertSame('/lolissr/manga/one-piece/1', $controller->jsonData['redirect']);
    }

    public function testAjaxUpdateNoteReturns500WhenUpdateFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['jacquette'] = '2';
        $_POST['livre_note'] = '4';
        $_POST['commentaire'] = 'Correct';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece'
        ];

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = $manga;
        $model->updateMangaReturn = false;

        $validator = new FakeValidator();
        $validator->failsReturn = false;

        $controller = new TestableMangaController($model);
        $controller->fakeValidator = $validator;

        $controller->ajaxUpdateNote('one-piece', '1');

        $this->assertSame(500, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('Erreur lors de la mise à jour', $controller->jsonData['message']);
    }

    public function testAjouterTraitementRedirectsWithSuccessWhenInsertSucceeds(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['livre'] = 'One Piece';
        $_POST['slug'] = 'one-piece';
        $_POST['numero'] = '1';
        $_POST['commentaire'] = 'Très bon tome';

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = false;
        $model->insertReturn = true;

        $validator = new FakeValidator();
        $validator->failsReturn = false;

        $controller = new TestableMangaController($model);
        $controller->fakeValidator = $validator;
        $controller->testUploadMode = false;

        $controller->ajouterTraitement();

        $this->assertSame('manga/ajouter', $controller->successRedirectPath);
        $this->assertSame('Manga ajouté avec succès', $controller->successRedirectMessage);

        $this->assertSame('one-piece-01', $model->insertArgs['thumbnail']);
        $this->assertSame('jpg', $model->insertArgs['extension']);
        $this->assertSame('one-piece', $model->insertArgs['slug']);
        $this->assertSame('One Piece', $model->insertArgs['livre']);
        $this->assertSame(1, $model->insertArgs['numero']);
        $this->assertNull($model->insertArgs['jacquette']);
        $this->assertNull($model->insertArgs['livre_note']);
        $this->assertSame('Très bon tome', $model->insertArgs['commentaire']);
    }

    public function testAjouterTraitementRedirectsWithErrorWhenInsertFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['livre'] = 'One Piece';
        $_POST['slug'] = 'one-piece';
        $_POST['numero'] = '1';
        $_POST['commentaire'] = 'Très bon tome';

        $model = new FakeMangaModel();
        $model->findOneBySlugAndNumeroReturn = false;
        $model->insertReturn = false;

        $validator = new FakeValidator();
        $validator->failsReturn = false;

        $controller = new TestableMangaController($model);
        $controller->fakeValidator = $validator;
        $controller->testUploadMode = false;

        $controller->ajouterTraitement();

        $this->assertSame('manga/ajouter', $controller->errorRedirectPath);
        $this->assertSame('Erreur lors de l’enregistrement du manga', $controller->errorRedirectMessage);
    }

    public function testAjouterTraitementReturns405JsonWhenAjaxRequestMethodIsNotPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new TestableMangaController(new FakeMangaModel());
        $controller->ajaxRequest = true;

        $controller->ajouterTraitement();

        $this->assertSame(405, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('Méthode non autorisée', $controller->jsonData['message']);
    }

    public function testAjouterTraitementReturns422JsonWhenAjaxValidatorFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $validator = new FakeValidator();
        $validator->failsReturn = true;
        $validator->errorsReturn = [
            'livre' => 'Le titre est obligatoire.'
        ];

        $controller = new TestableMangaController(new FakeMangaModel());
        $controller->fakeValidator = $validator;
        $controller->ajaxRequest = true;

        $controller->ajouterTraitement();

        $this->assertSame(422, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('Le titre est obligatoire.', $controller->jsonData['message']);
        $this->assertSame(
            ['livre' => 'Le titre est obligatoire.'],
            $controller->jsonData['errors']
        );
    }
}

final class TestableMangaController extends MangaController
{
    public ?string $renderedView = null;
    public array $renderedData = [];
    public ?string $notFoundMessage = null;
    public ?string $methodNotAllowedMessage = null;
    public bool $ajaxRequest = false;

    public array $jsonData = [];
    public int $jsonStatus = 200;

    public ?string $validationErrorPath = null;
    public array $validationErrorErrors = [];

    public ?string $errorRedirectPath = null;
    public ?string $errorRedirectMessage = null;

    public ?string $successRedirectPath = null;
    public ?string $successRedirectMessage = null;

    public bool $testUploadMode = false;

    public ?FakeValidator $fakeValidator = null;

    private MangaModel $fakeModel;

    public function __construct(MangaModel $fakeModel)
    {
        $this->fakeModel = $fakeModel;
        $this->title = '';
        $this->basePath = '/lolissr/';
    }

    protected function mangaModel(): MangaModel
    {
        return $this->fakeModel;
    }

    protected function makeValidator(array $post, array $files): Validator
    {
        return $this->fakeValidator ?? new FakeValidator();
    }

    protected function isAjaxRequest(): bool
    {
        return $this->ajaxRequest;
    }

    protected function isTestUploadMode(): bool
    {
        return $this->testUploadMode;
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        $this->jsonData = $data;
        $this->jsonStatus = $statusCode;
    }

    protected function redirectToCanonicalUrl(
        string $requestedSlug,
        string $canonicalSlug,
        string $pathPrefix,
        ?int $numero = null
    ): void
    {
        if ($requestedSlug === $canonicalSlug)
        {
            return;
        }

        $location = trim($pathPrefix, '/') . '/' . rawurlencode($canonicalSlug);

        if ($numero !== null)
        {
            $location .= '/' . $numero;
        }

        $this->successRedirectPath = $location;
    }

    public function callNormalizePostedNote(?string $value): ?int
    {
        return $this->normalizePostedNote($value);
    }

    public function exposedTitle(): string
    {
        return $this->title;
    }

    public function render(string $file, array $data = []): void
    {
        $this->renderedView = $file;
        $this->renderedData = $data;
    }

    protected function renderPartial(string $file, array $data = []): void
    {
        $this->renderedView = $file;
        $this->renderedData = $data;
    }

    protected function notFound(string $message = 'Page introuvable'): void
    {
        $this->notFoundMessage = $message;
    }

    protected function methodNotAllowed(string $message = 'Méthode non autorisée'): void
    {
        $this->methodNotAllowedMessage = $message;
    }

    protected function serverError(string $message = 'Erreur interne du serveur'): void
    {
    }

    protected function redirectWithValidationErrors(
        string $url,
        array $errors,
        string $message = 'Le formulaire contient des erreurs.'
    ): void
    {
        $this->validationErrorPath = $url;
        $this->validationErrorErrors = $errors;
    }

    protected function redirectWithError(
        string $url,
        string $message,
        bool $withOld = true
    ): void
    {
        $this->errorRedirectPath = $url;
        $this->errorRedirectMessage = $message;
    }

    protected function redirectWithSuccess(string $url, string $message): void
    {
        $this->successRedirectPath = $url;
        $this->successRedirectMessage = $message;
    }

    protected function uploadThumbnail(string $livre, int $numero): array
    {
        return [
            'thumbnail' => 'one-piece-01',
            'extension' => 'jpg',
            'destination' => ROOT . '/tests/Http/tmp-uploads/one-piece-01.jpg'
        ];
    }
}

final class FakeMangaModel extends MangaModel
{
    public int $countFirstTomesPaginateReturn = 1;
    public array $findAllFirstTomesReturn = [];
    public array $searchMangasReturn = [];
    public array $findBySlugReturn = [];
    public object|false $findOneBySlugAndNumeroReturn = false;
    public array $findOneBySlugAndNumeroSequence = [];
    public bool $updateMangaReturn = true;
    public bool $insertReturn = true;
    public array $updateMangaArgs = [];
    public array $insertArgs = [];

    public function __construct()
    {
    }

    public function countFirstTomesPaginate(int $eachPerPage): int
    {
        return $this->countFirstTomesPaginateReturn;
    }

    public function findAllFirstTomes(string $orderBy, int $eachPerPage, int $page): array
    {
        return $this->findAllFirstTomesReturn;
    }

    public function searchMangas(string $search): array
    {
        return $this->searchMangasReturn;
    }

    public function findBySlug(string $slug): array
    {
        return $this->findBySlugReturn;
    }

    public function findOneBySlugAndNumero(string $slug, int $numero): object|false
    {
        if ($this->findOneBySlugAndNumeroSequence !== [])
        {
            return array_shift($this->findOneBySlugAndNumeroSequence);
        }

        return $this->findOneBySlugAndNumeroReturn;
    }

    public function updateManga(
        string $slug,
        int $numero,
        ?int $jacquette,
        ?int $livreNote,
        ?string $commentaire
    ): bool {
        $this->updateMangaArgs = [
            'slug' => $slug,
            'numero' => $numero,
            'jacquette' => $jacquette,
            'livre_note' => $livreNote,
            'commentaire' => $commentaire
        ];

        return $this->updateMangaReturn;
    }

    public function insert(array $data): bool
    {
        $this->insertArgs = $data;

        return $this->insertReturn;
    }
}

final class FakeValidator extends Validator
{
    public bool $failsReturn = false;
    public array $errorsReturn = [];

    public function __construct()
    {
        parent::__construct([], []);
    }

    public function required(string $field, ?string $message = null): Validator
    {
        return $this;
    }

    public function string(string $field, ?string $message = null): Validator
    {
        return $this;
    }

    public function integer(string $field, ?string $message = null): Validator
    {
        return $this;
    }

    public function min(string $field, int|float $min, ?string $message = null): Validator
    {
        return $this;
    }

    public function max(string $field, int|float $max, ?string $message = null): Validator
    {
        return $this;
    }

    public function maxLength(string $field, int $max, ?string $message = null): Validator
    {
        return $this;
    }

    public function nullable(string $field): Validator
    {
        return $this;
    }

    public function fileRequired(string $field, ?string $message = null): Validator
    {
        return $this;
    }

    public function fileOk(string $field, ?string $message = null): Validator
    {
        return $this;
    }

    public function imageExtension(string $field, array $extensions, ?string $message = null): Validator
    {
        return $this;
    }

    public function imageMime(string $field, array $mimeTypes, ?string $message = null): Validator
    {
        return $this;
    }

    public function maxFileSize(string $field, int $maxSize, ?string $message = null): Validator
    {
        return $this;
    }

    public function fails(): bool
    {
        return $this->failsReturn;
    }

    public function errors(): array
    {
        return $this->errorsReturn;
    }
}