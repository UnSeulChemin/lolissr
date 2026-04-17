<?php

declare(strict_types=1);

use App\Controllers\MangaController;
use App\Models\MangaModel;
use PHPUnit\Framework\TestCase;

final class MangaControllerTest extends TestCase
{
    public function testNormalizePostedNoteReturnsNullWhenValueIsNull(): void
    {
        $controller = new TestableMangaController($this->createMock(MangaModel::class));

        $this->assertNull($controller->callNormalizePostedNote(null));
    }

    public function testNormalizePostedNoteReturnsNullWhenValueIsEmpty(): void
    {
        $controller = new TestableMangaController($this->createMock(MangaModel::class));

        $this->assertNull($controller->callNormalizePostedNote(''));
        $this->assertNull($controller->callNormalizePostedNote('   '));
    }

    public function testNormalizePostedNoteReturnsNullWhenValueIsOutOfRange(): void
    {
        $controller = new TestableMangaController($this->createMock(MangaModel::class));

        $this->assertNull($controller->callNormalizePostedNote('0'));
        $this->assertNull($controller->callNormalizePostedNote('6'));
        $this->assertNull($controller->callNormalizePostedNote('-1'));
    }

    public function testNormalizePostedNoteReturnsIntegerWhenValueIsValid(): void
    {
        $controller = new TestableMangaController($this->createMock(MangaModel::class));

        $this->assertSame(1, $controller->callNormalizePostedNote('1'));
        $this->assertSame(3, $controller->callNormalizePostedNote('3'));
        $this->assertSame(5, $controller->callNormalizePostedNote('5'));
    }

    public function testCollectionCallsNotFoundWhenPageIsNotNumeric(): void
    {
        $model = $this->createMock(MangaModel::class);
        $controller = new TestableMangaController($model);

        $controller->collection('abc');

        $this->assertSame('Page introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testCollectionCallsNotFoundWhenPageIsLowerThanOne(): void
    {
        $model = $this->createMock(MangaModel::class);
        $controller = new TestableMangaController($model);

        $controller->collection('0');

        $this->assertSame('Page introuvable', $controller->notFoundMessage);
        $this->assertNull($controller->renderedView);
    }

    public function testCollectionCallsNotFoundWhenPageIsGreaterThanLastPage(): void
    {
        $model = $this->createMock(MangaModel::class);
        $model->method('countFirstTomesPaginate')->willReturn(3);

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

        $model = $this->createMock(MangaModel::class);
        $model->method('countFirstTomesPaginate')->willReturn(3);
        $model->method('findAllFirstTomes')->willReturn($mangas);

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
}

final class TestableMangaController extends MangaController
{
    public ?string $renderedView = null;
    public array $renderedData = [];
    public ?string $notFoundMessage = null;

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
    }

    protected function serverError(string $message = 'Erreur interne du serveur'): void
    {
    }
}