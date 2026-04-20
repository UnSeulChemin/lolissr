<?php

declare(strict_types=1);

use App\Controllers\MangaController;
use App\Services\MangaReadService;
use App\Services\MangaService;
use PHPUnit\Framework\TestCase;

final class MangaControllerTest extends TestCase
{
    public function testIndexRendersMangaIndex(): void
    {
        $controller = $this->makeController();

        $controller->index();

        $this->assertSame('Manga', $controller->exposedTitle());
        $this->assertSame('manga/index', $controller->renderedView);
    }

    public function testLienRendersLienView(): void
    {
        $controller = $this->makeController();

        $controller->lien();

        $this->assertSame('Manga | Lien', $controller->exposedTitle());
        $this->assertSame('manga/lien', $controller->renderedView);
    }

    public function testAjouterRendersAjouterView(): void
    {
        $controller = $this->makeController();

        $controller->ajouter();

        $this->assertSame('Manga | Ajouter', $controller->exposedTitle());
        $this->assertSame('manga/ajouter', $controller->renderedView);
    }

    public function testCollectionCallsNotFoundWhenPageIsInvalid(): void
    {
        $readService = new FakeMangaReadService();
        $readService->collectionReturn = null;

        $controller = $this->makeController($readService);

        $controller->collection('abc');

        $this->assertSame('Page introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testCollectionRendersExpectedViewForValidPage(): void
    {
        $mangas = [
            (object) [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece',
            ],
        ];

        $readService = new FakeMangaReadService();
        $readService->collectionReturn = [
            'mangas' => $mangas,
            'compteur' => 3,
            'currentPage' => 2,
        ];

        $controller = $this->makeController($readService);

        $controller->collection('2');

        $this->assertNull($controller->notFoundMessage);
        $this->assertSame('Manga | Collection - Page 2', $controller->exposedTitle());
        $this->assertSame('manga/collection', $controller->renderedView);
        $this->assertSame($mangas, $controller->renderedData['mangas']);
        $this->assertSame(3, $controller->renderedData['compteur']);
        $this->assertNull($controller->renderedData['slugFilter']);
        $this->assertSame(2, $controller->renderedData['currentPage']);
    }

    public function testCollectionAjaxCallsNotFoundWhenPageIsInvalid(): void
    {
        $readService = new FakeMangaReadService();
        $readService->collectionReturn = null;

        $controller = $this->makeController($readService);

        $controller->collectionAjax('0');

        $this->assertSame('Page introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testCollectionAjaxRendersPartialForValidPage(): void
    {
        $mangas = [
            (object) [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece',
            ],
        ];

        $readService = new FakeMangaReadService();
        $readService->collectionReturn = [
            'mangas' => $mangas,
            'compteur' => 3,
            'currentPage' => 2,
        ];

        $controller = $this->makeController($readService);

        $controller->collectionAjax('2');

        $this->assertNull($controller->notFoundMessage);
        $this->assertSame('manga/partials/collection_ajax', $controller->renderedView);
        $this->assertSame($mangas, $controller->renderedData['mangas']);
        $this->assertSame(3, $controller->renderedData['compteur']);
        $this->assertNull($controller->renderedData['slugFilter']);
        $this->assertSame(2, $controller->renderedData['currentPage']);
    }

    public function testRechercheRendersEmptyResultsWhenQueryIsEmpty(): void
    {
        $readService = new FakeMangaReadService();
        $readService->searchReturn = [
            'mangas' => [],
            'search' => '',
        ];

        $controller = $this->makeController($readService);

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
                'livre' => 'One Piece',
            ],
        ];

        $readService = new FakeMangaReadService();
        $readService->searchReturn = [
            'mangas' => $results,
            'search' => 'one piece',
        ];

        $controller = $this->makeController($readService);

        $controller->recherche('one-piece');

        $this->assertSame('Manga | Recherche : one piece', $controller->exposedTitle());
        $this->assertSame('manga/search', $controller->renderedView);
        $this->assertSame($results, $controller->renderedData['mangas']);
        $this->assertSame('one piece', $controller->renderedData['search']);
    }

    public function testSearchAjaxReturnsEmptyArrayWhenQueryIsEmpty(): void
    {
        $readService = new FakeMangaReadService();
        $readService->searchAjaxReturn = [];

        $controller = $this->makeController($readService);

        $controller->searchAjax('');

        $this->assertSame(200, $controller->jsonStatus);
        $this->assertSame([], $controller->jsonData);
    }

    public function testSearchAjaxReturnsFormattedResults(): void
    {
        $results = [
            [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece',
                'thumbnail' => 'one-piece-01',
                'extension' => 'jpg',
                'note' => 9,
            ],
        ];

        $readService = new FakeMangaReadService();
        $readService->searchAjaxReturn = $results;

        $controller = $this->makeController($readService);

        $controller->searchAjax('one-piece');

        $this->assertSame(200, $controller->jsonStatus);
        $this->assertSame($results, $controller->jsonData);
    }

    public function testSerieCallsNotFoundWhenSlugDoesNotExist(): void
    {
        $readService = new FakeMangaReadService();
        $readService->serieReturn = null;

        $controller = $this->makeController($readService);

        $controller->serie('unknown-slug');

        $this->assertSame('Manga introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testSerieRedirectsWhenSlugIsNotCanonical(): void
    {
        $mangas = [
            (object) [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece',
            ],
        ];

        $readService = new FakeMangaReadService();
        $readService->serieReturn = [
            'canonicalSlug' => 'one-piece',
            'mangas' => $mangas,
        ];

        $controller = $this->makeController($readService);

        $controller->serie('One Piece');

        $this->assertSame('manga/serie/one-piece', $controller->redirectPath);
    }

    public function testSerieRendersCollectionWhenSlugExists(): void
    {
        $mangas = [
            (object) [
                'slug' => 'one-piece',
                'numero' => 1,
                'livre' => 'One Piece',
            ],
            (object) [
                'slug' => 'one-piece',
                'numero' => 2,
                'livre' => 'One Piece',
            ],
        ];

        $readService = new FakeMangaReadService();
        $readService->serieReturn = [
            'canonicalSlug' => 'one-piece',
            'mangas' => $mangas,
        ];

        $controller = $this->makeController($readService);

        $controller->serie('one-piece');

        $this->assertSame('Manga | One Piece', $controller->exposedTitle());
        $this->assertSame('manga/collection', $controller->renderedView);
        $this->assertSame($mangas, $controller->renderedData['mangas']);
        $this->assertNull($controller->renderedData['compteur']);
        $this->assertSame('one-piece', $controller->renderedData['slugFilter']);
        $this->assertSame(1, $controller->renderedData['currentPage']);
    }

    public function testShowCallsNotFoundWhenMangaDoesNotExist(): void
    {
        $readService = new FakeMangaReadService();
        $readService->oneReturn = null;

        $controller = $this->makeController($readService);

        $controller->show('one-piece', '20');

        $this->assertSame('Manga introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testShowRedirectsWhenSlugIsNotCanonical(): void
    {
        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 20,
            'livre' => 'One Piece',
        ];

        $readService = new FakeMangaReadService();
        $readService->oneReturn = [
            'canonicalSlug' => 'one-piece',
            'manga' => $manga,
        ];

        $controller = $this->makeController($readService);

        $controller->show('One Piece', '20');

        $this->assertSame('manga/one-piece/20', $controller->redirectPath);
    }

    public function testShowRendersLivreWhenMangaExists(): void
    {
        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 20,
            'livre' => 'One Piece',
        ];

        $readService = new FakeMangaReadService();
        $readService->oneReturn = [
            'canonicalSlug' => 'one-piece',
            'manga' => $manga,
        ];

        $controller = $this->makeController($readService);

        $controller->show('one-piece', '20');

        $this->assertSame('Manga | One Piece', $controller->exposedTitle());
        $this->assertSame('manga/livre', $controller->renderedView);
        $this->assertSame($manga, $controller->renderedData['manga']);
    }

    public function testModifierCallsNotFoundWhenMangaDoesNotExist(): void
    {
        $readService = new FakeMangaReadService();
        $readService->oneReturn = null;

        $controller = $this->makeController($readService);

        $controller->modifier('one-piece', '20');

        $this->assertSame('Manga introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testModifierRedirectsWhenSlugIsNotCanonical(): void
    {
        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 20,
            'livre' => 'One Piece',
        ];

        $readService = new FakeMangaReadService();
        $readService->oneReturn = [
            'canonicalSlug' => 'one-piece',
            'manga' => $manga,
        ];

        $controller = $this->makeController($readService);

        $controller->modifier('One Piece', '20');

        $this->assertSame('manga/modifier/one-piece/20', $controller->redirectPath);
    }

    public function testModifierRendersModifierViewWhenMangaExists(): void
    {
        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 20,
            'livre' => 'One Piece',
        ];

        $readService = new FakeMangaReadService();
        $readService->oneReturn = [
            'canonicalSlug' => 'one-piece',
            'manga' => $manga,
        ];

        $controller = $this->makeController($readService);

        $controller->modifier('one-piece', '20');

        $this->assertSame('Manga | Modifier', $controller->exposedTitle());
        $this->assertSame('manga/modifier', $controller->renderedView);
        $this->assertSame($manga, $controller->renderedData['manga']);
    }

    public function testAjouterTraitementCallsMethodNotAllowedWhenRequestMethodIsNotPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = $this->makeController();

        $controller->ajouterTraitement();

        $this->assertSame(
            'Méthode non autorisée pour l’ajout d’un manga',
            $controller->methodNotAllowedMessage
        );
    }

    public function testAjouterTraitementRedirectsWithValidationErrorsWhenCreateFailsWith422(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $service = new FakeMangaService();
        $service->createReturn = [
            'success' => false,
            'status' => 422,
            'message' => 'Le formulaire contient des erreurs.',
            'errors' => [
                'livre' => 'Le titre est obligatoire.',
            ],
        ];

        $controller = $this->makeController(null, $service);

        $controller->ajouterTraitement();

        $this->assertSame('manga/ajouter', $controller->validationErrorPath);
        $this->assertSame(
            ['livre' => 'Le titre est obligatoire.'],
            $controller->validationErrorErrors
        );
    }

    public function testAjouterTraitementRedirectsWithErrorWhenCreateFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $service = new FakeMangaService();
        $service->createReturn = [
            'success' => false,
            'status' => 500,
            'message' => 'Erreur lors de l’enregistrement du manga',
        ];

        $controller = $this->makeController(null, $service);

        $controller->ajouterTraitement();

        $this->assertSame('manga/ajouter', $controller->errorRedirectPath);
        $this->assertSame(
            'Erreur lors de l’enregistrement du manga',
            $controller->errorRedirectMessage
        );
    }

    public function testAjouterTraitementRedirectsWithSuccessWhenCreateSucceeds(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $service = new FakeMangaService();
        $service->createReturn = [
            'success' => true,
            'message' => 'Manga ajouté avec succès',
        ];

        $controller = $this->makeController(null, $service);

        $controller->ajouterTraitement();

        $this->assertSame('manga/ajouter', $controller->successRedirectPath);
        $this->assertSame('Manga ajouté avec succès', $controller->successRedirectMessage);
    }

    public function testAjouterTraitementReturns405JsonWhenAjaxRequestMethodIsNotPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = $this->makeController();
        $controller->ajaxRequest = true;

        $controller->ajouterTraitement();

        $this->assertSame(405, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('Méthode non autorisée', $controller->jsonData['message']);
    }

    public function testAjouterTraitementReturns422JsonWhenAjaxCreateFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $service = new FakeMangaService();
        $service->createReturn = [
            'success' => false,
            'status' => 422,
            'message' => 'Le titre est obligatoire.',
            'errors' => [
                'livre' => 'Le titre est obligatoire.',
            ],
        ];

        $controller = $this->makeController(null, $service);
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

    public function testUpdateCallsMethodNotAllowedWhenRequestMethodIsNotPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = $this->makeController();

        $controller->update('one-piece', '1');

        $this->assertSame(
            'Méthode non autorisée pour la modification d’un manga',
            $controller->methodNotAllowedMessage
        );
    }

    public function testUpdateCallsNotFoundWhenMangaDoesNotExist(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $readService = new FakeMangaReadService();
        $readService->oneReturn = null;

        $controller = $this->makeController($readService);

        $controller->update('one-piece', '1');

        $this->assertSame('Manga introuvable', $controller->notFoundMessage);
    }

    public function testUpdateReturns409JsonWhenSlugIsNotCanonicalInAjax(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece',
        ];

        $readService = new FakeMangaReadService();
        $readService->oneReturn = [
            'canonicalSlug' => 'one-piece',
            'manga' => $manga,
        ];

        $controller = $this->makeController($readService);
        $controller->ajaxRequest = true;

        $controller->update('One Piece', '1');

        $this->assertSame(409, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('URL non canonique', $controller->jsonData['message']);
        $this->assertSame('/lolissr/manga/modifier/one-piece/1', $controller->jsonData['redirect']);
    }

    public function testUpdateRedirectsWithValidationErrorsWhenUpdateFailsWith422(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece',
        ];

        $readService = new FakeMangaReadService();
        $readService->oneReturn = [
            'canonicalSlug' => 'one-piece',
            'manga' => $manga,
        ];

        $service = new FakeMangaService();
        $service->updateReturn = [
            'success' => false,
            'status' => 422,
            'message' => 'Le formulaire contient des erreurs.',
            'errors' => [
                'commentaire' => 'Le commentaire est invalide.',
            ],
        ];

        $controller = $this->makeController($readService, $service);

        $controller->update('one-piece', '1');

        $this->assertSame('manga/modifier/one-piece/1', $controller->validationErrorPath);
        $this->assertSame(
            ['commentaire' => 'Le commentaire est invalide.'],
            $controller->validationErrorErrors
        );
    }

    public function testUpdateRedirectsWithErrorWhenUpdateFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece',
        ];

        $readService = new FakeMangaReadService();
        $readService->oneReturn = [
            'canonicalSlug' => 'one-piece',
            'manga' => $manga,
        ];

        $service = new FakeMangaService();
        $service->updateReturn = [
            'success' => false,
            'status' => 500,
            'message' => 'Erreur lors de la mise à jour',
        ];

        $controller = $this->makeController($readService, $service);

        $controller->update('one-piece', '1');

        $this->assertSame('manga/modifier/one-piece/1', $controller->errorRedirectPath);
        $this->assertSame('Erreur lors de la mise à jour', $controller->errorRedirectMessage);
    }

    public function testUpdateRedirectsWithSuccessWhenUpdateSucceeds(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece',
        ];

        $fresh = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'jacquette' => 4,
            'livre_note' => 5,
            'note' => 9,
        ];

        $readService = new FakeMangaReadService();
        $readService->oneSequence = [
            ['canonicalSlug' => 'one-piece', 'manga' => $manga],
            ['canonicalSlug' => 'one-piece', 'manga' => $fresh],
        ];

        $service = new FakeMangaService();
        $service->updateReturn = [
            'success' => true,
            'message' => 'Manga mis à jour avec succès',
        ];

        $controller = $this->makeController($readService, $service);

        $controller->update('one-piece', '1');

        $this->assertSame('manga/one-piece/1', $controller->successRedirectPath);
        $this->assertSame('Manga mis à jour avec succès', $controller->successRedirectMessage);
    }

    public function testAjaxUpdateNoteReturns405WhenRequestMethodIsNotPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = $this->makeController();

        $controller->ajaxUpdateNote('one-piece', '1');

        $this->assertSame(405, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('Méthode non autorisée', $controller->jsonData['message']);
    }

    public function testAjaxUpdateNoteReturns404WhenMangaDoesNotExist(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $readService = new FakeMangaReadService();
        $readService->oneReturn = null;

        $controller = $this->makeController($readService);

        $controller->ajaxUpdateNote('one-piece', '1');

        $this->assertSame(404, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('Manga introuvable', $controller->jsonData['message']);
    }

    public function testAjaxUpdateNoteReturns409WhenSlugIsNotCanonical(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece',
        ];

        $readService = new FakeMangaReadService();
        $readService->oneReturn = [
            'canonicalSlug' => 'one-piece',
            'manga' => $manga,
        ];

        $controller = $this->makeController($readService);

        $controller->ajaxUpdateNote('One Piece', '1');

        $this->assertSame(409, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('URL non canonique', $controller->jsonData['message']);
        $this->assertSame('/lolissr/manga/modifier/one-piece/1', $controller->jsonData['redirect']);
    }

    public function testAjaxUpdateNoteReturns422WhenUpdateFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece',
        ];

        $readService = new FakeMangaReadService();
        $readService->oneReturn = [
            'canonicalSlug' => 'one-piece',
            'manga' => $manga,
        ];

        $service = new FakeMangaService();
        $service->updateReturn = [
            'success' => false,
            'status' => 422,
            'message' => 'La note jacquette doit être un entier.',
            'errors' => [
                'jacquette' => 'La note jacquette doit être un entier.',
            ],
        ];

        $controller = $this->makeController($readService, $service);

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

    public function testAjaxUpdateNoteReturns500WhenUpdateFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $manga = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece',
        ];

        $readService = new FakeMangaReadService();
        $readService->oneReturn = [
            'canonicalSlug' => 'one-piece',
            'manga' => $manga,
        ];

        $service = new FakeMangaService();
        $service->updateReturn = [
            'success' => false,
            'status' => 500,
            'message' => 'Erreur lors de la mise à jour',
        ];

        $controller = $this->makeController($readService, $service);

        $controller->ajaxUpdateNote('one-piece', '1');

        $this->assertSame(500, $controller->jsonStatus);
        $this->assertFalse($controller->jsonData['success']);
        $this->assertSame('Erreur lors de la mise à jour', $controller->jsonData['message']);
    }

    public function testAjaxUpdateNoteReturnsSuccessWhenUpdateSucceeds(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $existing = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'livre' => 'One Piece',
        ];

        $fresh = (object) [
            'slug' => 'one-piece',
            'numero' => 1,
            'jacquette' => 2,
            'livre_note' => 4,
            'note' => 6,
        ];

        $readService = new FakeMangaReadService();
        $readService->oneSequence = [
            ['canonicalSlug' => 'one-piece', 'manga' => $existing],
            ['canonicalSlug' => 'one-piece', 'manga' => $fresh],
        ];

        $service = new FakeMangaService();
        $service->updateReturn = [
            'success' => true,
            'message' => 'Manga mis à jour avec succès',
        ];

        $controller = $this->makeController($readService, $service);

        $controller->ajaxUpdateNote('one-piece', '1');

        $this->assertSame(200, $controller->jsonStatus);
        $this->assertTrue($controller->jsonData['success']);
        $this->assertSame('Notes mises à jour', $controller->jsonData['message']);
        $this->assertSame(2, $controller->jsonData['jacquette']);
        $this->assertSame(4, $controller->jsonData['livre_note']);
        $this->assertSame(6, $controller->jsonData['note']);
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
        $_SERVER = [];
    }

    private function makeController(
        ?FakeMangaReadService $readService = null,
        ?FakeMangaService $service = null
    ): TestableMangaController {
        return new TestableMangaController(
            $readService ?? new FakeMangaReadService(),
            $service ?? new FakeMangaService()
        );
    }
}

final class TestableMangaController extends MangaController
{
    public ?string $renderedView = null;
    public array $renderedData = [];
    public ?string $notFoundMessage = null;
    public ?string $methodNotAllowedMessage = null;

    public array $jsonData = [];
    public int $jsonStatus = 200;

    public ?string $validationErrorPath = null;
    public array $validationErrorErrors = [];

    public ?string $errorRedirectPath = null;
    public ?string $errorRedirectMessage = null;

    public ?string $successRedirectPath = null;
    public ?string $successRedirectMessage = null;

    public ?string $redirectPath = null;
    public int $redirectCode = 302;

    public bool $ajaxRequest = false;
    public bool $stopped = false;

    private FakeMangaReadService $fakeReadService;
    private FakeMangaService $fakeService;

    public function __construct(
        FakeMangaReadService $fakeReadService,
        FakeMangaService $fakeService
    ) {
        parent::__construct();

        $this->fakeReadService = $fakeReadService;
        $this->fakeService = $fakeService;
        $this->title = '';
        $this->basePath = '/lolissr/';
    }

    protected function mangaReadService(): MangaReadService
    {
        return $this->fakeReadService;
    }

    protected function mangaService(): MangaService
    {
        return $this->fakeService;
    }

    protected function isAjaxRequest(): bool
    {
        return $this->ajaxRequest;
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        $this->jsonData = $data;
        $this->jsonStatus = $statusCode;
        $this->stopped = true;
    }

    protected function redirectToCanonicalUrl(
        string $requestedSlug,
        string $canonicalSlug,
        string $pathPrefix,
        ?int $numero = null
    ): void {
        if ($requestedSlug === $canonicalSlug)
        {
            return;
        }

        $path = trim($pathPrefix, '/') . '/' . rawurlencode($canonicalSlug);

        if ($numero !== null)
        {
            $path .= '/' . $numero;
        }

        $this->redirectPath = $path;
        $this->redirectCode = 301;
        $this->stopped = true;
    }

    protected function handleCanonicalUpdateAccess(
        string $requestedSlug,
        object $manga,
        int $numero,
        bool $ajax = false
    ): string {
        $canonicalSlug = \App\Core\Support\Str::slug((string) $manga->slug);
        $redirect = $this->basePath . 'manga/modifier/' . rawurlencode($canonicalSlug) . '/' . $numero;

        if ($requestedSlug === $canonicalSlug)
        {
            return $canonicalSlug;
        }

        if ($ajax)
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'URL non canonique',
                'redirect' => $redirect,
            ], 409);

            return $canonicalSlug;
        }

        $this->redirectPath = 'manga/modifier/' . rawurlencode($canonicalSlug) . '/' . $numero;
        $this->redirectCode = 301;
        $this->stopped = true;

        return $canonicalSlug;
    }

    protected function findCanonicalMangaOrFail(
        string $slug,
        int $numero,
        bool $ajax = false
    ): object {
        $data = $this->mangaReadService()->one($slug, $numero);

        if ($data !== null)
        {
            return $data['manga'];
        }

        if ($ajax)
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Manga introuvable',
            ], 404);
        }

        $this->notFound('Manga introuvable');

        return (object) [];
    }

    protected function performUpdate(string $slug, string $numero, bool $ajax = false): void
    {
        $requestedSlug = trim($slug);
        $numero = (int) $numero;

        if (!\App\Core\Http\Request::isPost())
        {
            if ($ajax)
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Méthode non autorisée',
                ], 405);

                return;
            }

            $this->methodNotAllowed('Méthode non autorisée pour la modification d’un manga');

            return;
        }

        $manga = $this->findCanonicalMangaOrFail($requestedSlug, $numero, $ajax);

        if ($this->stopped)
        {
            return;
        }

        $canonicalSlug = $this->handleCanonicalUpdateAccess(
            $requestedSlug,
            $manga,
            $numero,
            $ajax
        );

        if ($this->stopped)
        {
            return;
        }

        $result = $this->mangaService()->update(
            $canonicalSlug,
            $numero,
            \App\Core\Http\Request::allPost(),
            \App\Core\Http\Request::allFiles()
        );

        $this->handleUpdateResult($result, $canonicalSlug, $numero, $ajax);
    }

    public function render(string $file, array $data = []): void
    {
        if ($this->stopped)
        {
            return;
        }

        $this->renderedView = $file;
        $this->renderedData = $data;
    }

    protected function renderPartial(string $file, array $data = []): void
    {
        if ($this->stopped)
        {
            return;
        }

        $this->renderedView = $file;
        $this->renderedData = $data;
    }

    protected function notFound(string $message = 'Page introuvable'): void
    {
        $this->notFoundMessage = $message;
        $this->stopped = true;
    }

    protected function methodNotAllowed(string $message = 'Méthode non autorisée'): void
    {
        $this->methodNotAllowedMessage = $message;
        $this->stopped = true;
    }

    protected function serverError(string $message = 'Erreur interne du serveur'): void
    {
        $this->stopped = true;
    }

    protected function redirectWithValidationErrors(
        string $url,
        array $errors,
        string $message = 'Le formulaire contient des erreurs.'
    ): void
    {
        $this->validationErrorPath = $url;
        $this->validationErrorErrors = $errors;
        $this->stopped = true;
    }

    protected function redirectWithError(
        string $url,
        string $message,
        bool $withOld = true
    ): void
    {
        $this->errorRedirectPath = $url;
        $this->errorRedirectMessage = $message;
        $this->stopped = true;
    }

    protected function redirectWithSuccess(string $url, string $message): void
    {
        $this->successRedirectPath = $url;
        $this->successRedirectMessage = $message;
        $this->stopped = true;
    }

    public function exposedTitle(): string
    {
        return $this->title;
    }
}

final class FakeMangaReadService extends MangaReadService
{
    public ?array $collectionReturn = null;
    public ?array $searchReturn = null;
    public array $searchAjaxReturn = [];
    public ?array $serieReturn = null;
    public ?array $oneReturn = null;
    public array $oneSequence = [];

    public function __construct()
    {
    }

    public function collection(string $page = '1'): ?array
    {
        return $this->collectionReturn;
    }

    public function search(string $query = ''): array
    {
        return $this->searchReturn ?? [
            'mangas' => [],
            'search' => '',
        ];
    }

    public function searchAjax(string $query = ''): array
    {
        return $this->searchAjaxReturn;
    }

    public function serie(string $slug): ?array
    {
        return $this->serieReturn;
    }

    public function one(string $slug, int $numero): ?array
    {
        if ($this->oneSequence !== [])
        {
            return array_shift($this->oneSequence);
        }

        return $this->oneReturn;
    }
}

final class FakeMangaService extends MangaService
{
    public array $createReturn = [
        'success' => true,
        'message' => 'Manga ajouté avec succès',
    ];

    public array $updateReturn = [
        'success' => true,
        'message' => 'Manga mis à jour avec succès',
    ];

    public array $createArgs = [];
    public array $updateArgs = [];

    public function __construct()
    {
    }

    public function create(array $post, array $files): array
    {
        $this->createArgs = [
            'post' => $post,
            'files' => $files,
        ];

        return $this->createReturn;
    }

    public function update(string $slug, int $numero, array $post, array $files): array
    {
        $this->updateArgs = [
            'slug' => $slug,
            'numero' => $numero,
            'post' => $post,
            'files' => $files,
        ];

        return $this->updateReturn;
    }
}